<?php

namespace Tests\Controllers;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../loadconfig.php';

use App\Controllers\ProgramController;
use App\Services\ProgramService;
use Core\DIContainer;
use PHPUnit\Framework\TestCase;

class ProgramControllerTest extends TestCase
{
    private ProgramController $programController;
    static private int $programId;

    protected function setUp(): void
    {
        $programService = DIContainer::resolve(ProgramService::class);
        $this->programController = $this->getMockBuilder(ProgramController::class)
            ->setConstructorArgs([$programService])
            ->onlyMethods(['hasRole', 'saveImageFile', 'deleteImageFile'])
            ->getMock();

        $this->programController->method('hasRole')->willReturn(true);
        $this->programController->method('saveImageFile')->willReturn('/uploads/images/test_image.png');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    public function testCreateProgram(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['description'] = 'Description for the test program.';
        $_POST['url'] = 'https://example.com';

        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        ob_start();
        $this->programController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals('Program created successfully.', $response['message']);
        $this->assertArrayHasKey('id', $response);
        $this->assertNotEmpty($response['id']);
        self::$programId = $response['id'];
    }

    public function testCreateProgramNoName(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['description'] = 'Description for the test program.';
        $_POST['url'] = 'https://example.com';

        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        ob_start();
        $this->programController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testGetProgramInvalidId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['id'] = 'invalid-id';

        ob_start();
        $this->programController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(400, http_response_code());
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid program ID.', $response['error']);
    }


    public function testDeleteProgram(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = self::$programId;

        ob_start();
        $this->programController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Program deleted successfully.', $response['message']);
    }
}
