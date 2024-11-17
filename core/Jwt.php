<?php

namespace Core;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * JWT (JSON Web Token) implementation with strict typing and enhanced security features.
 *
 * This class provides functionality to create and verify JWT tokens following RFC 7519.
 * It implements HS256 (HMAC-SHA256) signing algorithm and includes standard JWT claims.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7519 JWT RFC
 */
class Jwt
{
    /** Default token lifetime in seconds (3 hours) */
    private const int DEFAULT_LIFETIME = 3 * 60 * 60;

    /** JWT secret key for signing tokens */
    private static string $secret;

    /**
     * Initializes the JWT secret key
     *
     * @throws RuntimeException If the secret key is not properly configured
     */
    public static function __constructStatic(): void
    {
//        $secret = getenv('JWT_SECRET');
        $secret = "fYz2mh6JUkIwNBMKP92zgSNm+r+4UytqnujKBUin0iFv9dQ+zw14oYJByjyHt149";
        if (!$secret) {
            throw new RuntimeException('JWT_SECRET environment variable must be set');
        }

        if (strlen($secret) < 32) {
            throw new RuntimeException('JWT_SECRET must be at least 32 characters long');
        }

        self::$secret = $secret;
    }

    /**
     * Decodes Base64URL formatted data
     *
     * @param string $data Base64URL encoded data
     * @return string Decoded data
     * @throws InvalidArgumentException If the input is not valid Base64URL
     */
    private static function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;
        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid Base64URL encoding');
        }

        return $decoded;
    }

    /**
     * Encodes data to Base64URL format
     *
     * @param string $data Data to encode
     * @return string Base64URL encoded string
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Creates a new JWT token
     *
     * @param string $username Subject of the token
     * @return string Generated JWT token
     * @throws JsonException If JSON encoding fails
     */
    public static function create(string $username): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $now = new DateTimeImmutable();
        $registeredClaims = [
            'iss' => 'pseduca-backend',
            'sub' => $username,
            'aud' => ['pseduca-frontend'],
            'exp' => $now->getTimestamp() + self::DEFAULT_LIFETIME,
            'nbf' => $now->getTimestamp(),
            'iat' => $now->getTimestamp(),
            'jti' => bin2hex(random_bytes(16))
        ];

        // Roles are intentionally omitted from the token payload.
        // This is because roles may change during the token's lifetime, and there is
        // currently no mechanism implemented to revoke or invalidate tokens once issued.

        $base64UrlHeader = self::base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $base64UrlPayload = self::base64UrlEncode(json_encode($registeredClaims, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", self::$secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    /**
     * Verifies and decodes a JWT token
     *
     * @param string $token JWT token to verify and decode
     * @return array<string, mixed> Decoded token payload
     * @throws InvalidArgumentException If the token format is invalid
     * @throws RuntimeException If the token signature is invalid or the token has expired
     */
    public static function verify(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Invalid token format');
        }

        [$encHeader, $encPayload, $encSignature] = $parts;
        // Decode
        $header = self::base64UrlDecode($encHeader);
        $payload = self::base64UrlDecode($encPayload);
        $signature = self::base64UrlDecode($encSignature);

        // Verify the signature
        if (json_decode($header, true)['alg'] !== 'HS256') {
            throw new InvalidArgumentException('Unsupported JWT algorithm');
        }

        $signatureCheck = hash_hmac('sha256', "$encHeader.$encPayload", self::$secret, true);
        if (!hash_equals($signature, $signatureCheck)) {
            throw new RuntimeException('Invalid token signature');
        }

        // Verify the expiration time
        $payloadData = json_decode($payload, true);

        $now = new DateTimeImmutable();
        if (!isset($payloadData['exp'])) {
            throw new InvalidArgumentException('Token missing expiration claim');
        }

        if ($now->getTimestamp() > $payloadData['exp']) {
            throw new RuntimeException('Token has expired');
        }

        if (isset($payloadData['nbf']) && $now->getTimestamp() < $payloadData['nbf']) {
            throw new RuntimeException('Token not yet valid');
        }

        return $payloadData;
    }
}

Jwt::__constructStatic();
