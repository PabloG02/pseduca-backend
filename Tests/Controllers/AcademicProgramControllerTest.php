<?php

namespace Tests\Controllers;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../loadconfig.php';

use App\Controllers\AcademicProgramController;
use App\Filters\AcademicProgramFilter;
use App\Services\AcademicProgramService;
use Core\DIContainer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class AcademicProgramControllerTest extends TestCase
{
    private AcademicProgramController $academicProgramController;
    static private int $academicProgramId;

    protected function setUp(): void
    {
        $academicProgramService = DIContainer::resolve(AcademicProgramService::class);
        $this->academicProgramController = $this->getMockBuilder(AcademicProgramController::class)
            ->setConstructorArgs([$academicProgramService])
            ->onlyMethods(['hasRole', 'createFilterFromRequest', 'saveImageFile', 'deleteImageFile'])
            ->getMock();


        $this->academicProgramController->method('hasRole')
            ->willReturn(true);
        $this->academicProgramController->method('saveImageFile')
            ->willReturn('/uploads/images/academicprogs/test_image.png');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    public function testCreateAcademicProgram(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals('Academic program created successfully.', $response['message']);
        $this->assertArrayHasKey('id', $response);
        $this->assertNotEmpty($response['id']);
        self::$academicProgramId = $response['id'];
    }
    private function setRole($role): void
    {
        $_SESSION['role'] = $role;
    }

    public function testCreateAcademicProgramNoName(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // No name
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateAcademicProgramNoQualificationLevel(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        // No qualification level
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateAcademicProgramNoAvailableSlots(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        // No available slots
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateAcademicProgramNoTeachingType(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        // No teaching type
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateAcademicProgramNoOfferingFrequency(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        // No offering frequency
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateAcademicProgramNoDurationEcts(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        // No duration ects
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateAcademicProgramNoLocation(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        // No location
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateAcademicProgramInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
    }

    public function testCreateAcademicProgramInvalidImageType(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Program';
        $_POST['qualification_level'] = 'Master';
        $_POST['description'] = 'This is a test program.';
        $_POST['available_slots'] = 20;
        $_POST['teaching_type'] = 'Online';
        $_POST['offering_frequency'] = 'Annual';
        $_POST['duration_ects'] = 60;
        $_POST['location'] = 'Test Location';
        $_FILES['image_uri'] = [
            'name' => 'document.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid image file format. Only JPEG and PNG are allowed.', $response['error']);
    }


    #[Depends('testCreateAcademicProgram')]
    public function testGetAcademicProgram(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['id'] = self::$academicProgramId;

        ob_start();
        $this->academicProgramController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(self::$academicProgramId, $response['id']);
        $this->assertEquals('Test Program', $response['name']);
        $this->assertEquals('Master', $response['qualificationLevel']);
    }

    public function testGetAcademicProgramNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['id'] = 483427;

        ob_start();
        $this->academicProgramController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Academic program not found.', $response['error']);
    }


    public function testInvalidAcademicProgramId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET['id'] = 'invalid_id';

        ob_start();
        $this->academicProgramController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(400, http_response_code());

        $this->assertEquals('Invalid academic program ID.', $response['error']);
    }

    #[Depends('testCreateAcademicProgram')]
    public function testUpdateAcademicProgram(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = self::$academicProgramId;
        $_POST['name'] = 'Updated Program Name';
        $_POST['qualification_level'] = 'Doctorate';
        $_POST['description'] = 'Updated description';
        $_POST['available_slots'] = 25;
        $_POST['teaching_type'] = 'Onsite';
        $_POST['offering_frequency'] = 'Biannual';
        $_POST['duration_ects'] = 120;
        $_POST['location'] = 'Updated Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->update();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals('Academic program updated successfully.', $response['message']);
    }

    public function testUpdateAcademicProgramInvalidId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 'invalid_id';
        $_POST['name'] = 'Updated Program Name';
        $_POST['qualification_level'] = 'Doctorate';
        $_POST['description'] = 'Updated description';
        $_POST['available_slots'] = 25;
        $_POST['teaching_type'] = 'Onsite';
        $_POST['offering_frequency'] = 'Biannual';
        $_POST['duration_ects'] = 120;
        $_POST['location'] = 'Updated Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->update();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid academic program ID.', $response['error']);
    }

    public function testUpdateAcademicProgramInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = self::$academicProgramId;
        $_POST['name'] = 'Updated Program Name';
        $_POST['qualification_level'] = 'Doctorate';
        $_POST['description'] = 'Updated description';
        $_POST['available_slots'] = 25;
        $_POST['teaching_type'] = 'Onsite';
        $_POST['offering_frequency'] = 'Biannual';
        $_POST['duration_ects'] = 120;
        $_POST['location'] = 'Updated Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->update();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
    }

    public function testUpdateAcademicProgramNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 483427;
        $_POST['name'] = 'Updated Program Name';
        $_POST['qualification_level'] = 'Doctorate';
        $_POST['description'] = 'Updated description';
        $_POST['available_slots'] = 25;
        $_POST['teaching_type'] = 'Onsite';
        $_POST['offering_frequency'] = 'Biannual';
        $_POST['duration_ects'] = 120;
        $_POST['location'] = 'Updated Location';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');

        ob_start();
        $this->academicProgramController->update();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Academic program not found.', $response['error']);
    }

    #[Depends('testCreateAcademicProgram')]
    public function testListAcademicProgram(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $filterData = ['name' => 'Updated Program Name'];

        ob_start();
        $this->academicProgramController->method('createFilterFromRequest')
            ->willReturn(AcademicProgramFilter::fromArray($filterData));
        $this->academicProgramController->list();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue(isset($response['total_count']) && $response['total_count'] > 0);
        $this->assertArrayHasKey('name', $response['data'][0]);
        $this->assertEquals('Updated Program Name', $response['data'][0]['name']);
    }

    #[Depends('testCreateAcademicProgram')]
    #[Depends('testListAcademicProgram')]
    public function testDeleteAcademicProgram(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = self::$academicProgramId;

        ob_start();
        $this->academicProgramController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(200, http_response_code());
    }

    public function testDeleteAcademicProgramInvalidId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 'invalid-id';

        ob_start();
        $this->academicProgramController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(400, http_response_code());
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid academic program ID.', $response['error']);
    }

    public function testDeleteAcademicProgramNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 32849023;

        ob_start();
        $this->academicProgramController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Academic program not found.', $response['error']);
    }
}
