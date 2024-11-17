<?php

namespace Tests\Core;

require_once __DIR__ . '/../../autoload.php';

use Core\Jwt;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JwtTest extends TestCase
{
    public function testCreateReturnsValidJwtToken(): void
    {
        $token = Jwt::create('testuser');

        // Token should be a string with three parts separated by dots
        $this->assertIsString($token);
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);

        // Each part should be valid base64url
        foreach ($parts as $part) {
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $part);
        }

        // Verify the token is valid and contains expected claims
        $payload = Jwt::verify($token);
        $this->assertEquals('testuser', $payload['sub']);
        $this->assertEquals('pseduca-backend', $payload['iss']);
        $this->assertEquals(['pseduca-frontend'], $payload['aud']);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertArrayHasKey('nbf', $payload);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('jti', $payload);
    }

    public function testVerifyWithValidToken(): void
    {
        $token = Jwt::create('testuser');
        $payload = Jwt::verify($token);

        $now = new DateTimeImmutable();
        $this->assertIsArray($payload);
        $this->assertTrue($payload['exp'] > $now->getTimestamp());
        $this->assertTrue($payload['nbf'] <= $now->getTimestamp());
        $this->assertTrue($payload['iat'] <= $now->getTimestamp());
    }

    public function testVerifyWithExpiredToken(): void
    {
        // TODO: Implement test
    }

    public function testVerifyWithInvalidSignature(): void
    {
        $token = Jwt::create('testuser');
        $parts = explode('.', $token);
        // Modify the signature part
        $parts[2] = rtrim(strtr(base64_encode('invalid_signature'), '+/', '-_'), '=');
        $invalidToken = implode('.', $parts);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid token signature');

        Jwt::verify($invalidToken);
    }

    public function testVerifyWithInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid token format');

        Jwt::verify('invalid.token');
    }

    public function testVerifyWithInvalidBase64Url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Base64URL encoding');

        Jwt::verify('invalid!.token!.signature!');
    }

    public function testVerifyWithUnsupportedAlgorithm(): void
    {
        // Create a token with an unsupported algorithm
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'RS256']));
        $payload = base64_encode(json_encode(['sub' => 'testuser']));
        $signature = base64_encode('signature');
        $token = rtrim(strtr($header . '.' . $payload . '.' . $signature, '+/', '-_'), '=');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported JWT algorithm');

        Jwt::verify($token);
    }

    public function testVerifyWithFutureNbf(): void
    {
        // TODO: Implement test
    }

    public function testVerifyWithMissingExpClaim(): void
    {
        // TODO: Implement test
    }
}