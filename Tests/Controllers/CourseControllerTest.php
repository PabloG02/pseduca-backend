<?php

namespace Tests\Controllers;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../loadconfig.php';

use App\Controllers\CourseController;
use App\Filters\CourseFilter;
use App\Services\CourseService;
use Core\DIContainer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class CourseControllerTest extends TestCase
{
    private CourseController $courseController;
    static private int $courseId;


    protected function setUp(): void
    {
        // Set up the database connection and any other necessary configuration
        // This is where you might set up a mock database or use a test database
        $courseService = DIContainer::resolve(CourseService::class);
        $this->courseController = $this->getMockBuilder(CourseController::class)
            ->setConstructorArgs([$courseService])
            ->onlyMethods(['hasRole', 'createFilterFromRequest',  'saveImageFile', 'deleteImageFile'])
            ->getMock();

        $this->courseController->method('hasRole')
            ->willReturn(true);
        $this->courseController->method('saveImageFile')
            ->willReturn('/uploads/images/test_image.png');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    private function setRole($role): void
    {
        $_SESSION['role'] = $role;
    }

    public function testCreateCourse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Description for the test course.';
        $_POST['start_date'] = '2024-01-01';
        $_POST['end_date'] = '2024-06-01';
        $_POST['url'] = 'https://example.com';

        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->courseController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(201, http_response_code());

        $this->assertEquals('Course created successfully.', $response['message']);
        $this->assertArrayHasKey('id', $response);
        $this->assertNotEmpty($response['id']);
        self::$courseId = $response['id'];
    }

    public function testCreateCourseNoName(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // No name
        $_POST['description'] = 'Description for the test course.';
        $_POST['start_date'] = '2024-01-01';
        $_POST['end_date'] = '2024-06-01';
        $_POST['url'] = 'https://example.com';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->courseController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateCourseMissingStartDate(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Description for the test course.';
        $_POST['url'] = 'https://example.com';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        // Missing start_date
        $_POST['start_date'] = null;
        $_POST['end_date'] = '2024-06-01';

        ob_start();
        $this->courseController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateCourseMissingEndDate(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Description for the test course.';
        $_POST['url'] = 'https://example.com';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        // Missing end_date
        $_POST['start_date'] = '2024-06-01';
        $_POST['end_date'] = null;

        ob_start();
        $this->courseController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }
    
    public function testCreateCourseNoImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Description for the test course.';
        $_POST['start_date'] = '2024-01-01';
        $_POST['end_date'] = '2024-06-01';
        $_POST['url'] = 'https://example.com';

        $this->setRole('admin');

        ob_start();
        $this->courseController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateCourseInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Description for the test course.';
        $_POST['start_date'] = '2024-01-01';
        $_POST['end_date'] = '2024-06-01';
        $_POST['url'] = 'https://example.com';

        $_FILES['image_uri'] = [
            'name' => 'test_image.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();

        $this->courseController->create();

        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        
    }

    public function testCreateCourseInvalidImageType(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Description for the test course.';
        $_POST['start_date'] = '2024-01-01';
        $_POST['end_date'] = '2024-06-01';
        $_POST['url'] = 'https://example.com';

        $_FILES['image_uri'] = [
            'name' => 'document.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $this->setRole('admin');

        ob_start();
        $this->courseController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid image file format. Only JPEG and PNG are allowed.', $response['error']);
    }

    public function testInvalidCourseId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET['id'] = 'invalid_id';

        ob_start();
        $this->courseController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(400, http_response_code());

        $this->assertEquals('Invalid course ID.', $response['error']);
    }

    public function testGetTeamMemberNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['id'] = 483427;

        ob_start();
        $this->courseController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Course not found.', $response['error']);
    }

    #[Depends('testCreateCourse')]
    public function testUpdateCourse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT'; 
        $_POST['id'] = self::$courseId; 
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Updated description';
        $_POST['start_date'] = '2024-01-02';
        $_POST['end_date'] = '2024-06-02';
        $_POST['url'] = 'https://exampleupdate.com';
    
        $_FILES['image_uri'] = [
            'name' => 'test_imageupdate.png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
    
        $this->setRole('admin'); 
        
        ob_start();
        $this->courseController->update();
        $output = ob_get_clean();
    
        $response = json_decode($output, true);
    
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Course updated successfully.', $response['message']);
        $this->assertEquals(200, http_response_code());

    }

    public function testUpdateInvalidId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 'invalid-id';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Updated description';
        $_POST['start_date'] = '2024-01-02';
        $_POST['end_date'] = '2024-06-02';
        $_POST['url'] = 'https://exampleupdate.com';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->courseController->update();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid course ID.', $response['error']);
    }

    public function testUpdateTeamMemberInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Updated description';
        $_POST['start_date'] = '2024-01-02';
        $_POST['end_date'] = '2024-06-02';
        $_POST['url'] = 'https://exampleupdate.com';
        $_FILES['image_uri'] = [
            'name' => 'test_image',
            'type' => 'image',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];


        $this->setRole('admin');

        ob_start();
        $this->courseController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
    }

    public function testUpdateCourseNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 483427;
        $_POST['name'] = 'Test Course';
        $_POST['description'] = 'Updated description';
        $_POST['start_date'] = '2024-01-02';
        $_POST['end_date'] = '2024-06-02';
        $_POST['url'] = 'https://exampleupdate.com';
        $_FILES['image_uri'] = [
            'name' => 'test_image',
            'type' => 'image',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->courseController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Course not found.', $response['error']);
    }

    #[Depends('testCreateCourse')]
    public function testListCourse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $filterData = ['name' => 'Test Course'];

        ob_start();
        $this->courseController->method('createFilterFromRequest')
            ->willReturn(CourseFilter::fromArray($filterData));
        $this->courseController->list();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue(isset($response['total_count']) && $response['total_count'] > 0);
        $this->assertArrayHasKey('name', $response['data'][0]);
        $this->assertEquals('Test Course', $response['data'][0]['name']);
    }

    #[Depends('testCreateCourse')]
    #[Depends('testListCourse')]
    public function testDeleteCourse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = self::$courseId;

        ob_start();
        $this->courseController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(200, http_response_code());
    }

    public function testDeleteInvalidCourseId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 'invalid-id';

        ob_start();
        $this->courseController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(400, http_response_code());

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid course ID.', $response['error']);
    }

    public function testDeleteCourseNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 32849023;

        ob_start();
        $this->courseController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Course not found.', $response['error']);
    }
}