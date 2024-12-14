<?php

namespace Tests\Controllers;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../loadconfig.php';

use App\Controllers\TeamMemberController;
use App\Filters\TeamMemberFilter;
use App\Services\TeamMemberService;
use Core\DIContainer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class TeamMemberControllerTest extends TestCase
{
    private TeamMemberController $teamMemberController;
    static private int $teamMemberId;

    protected function setUp(): void
    {
        $teamMemberService = DIContainer::resolve(TeamMemberService::class);
        $this->teamMemberController = $this->getMockBuilder(TeamMemberController::class)
            ->setConstructorArgs([$teamMemberService])
            ->onlyMethods(['hasRole', 'createFilterFromRequest', 'saveImageFile', 'deleteImageFile'])
            ->getMock();


        $this->teamMemberController->method('hasRole')
            ->willReturn(true);
        $this->teamMemberController->method('saveImageFile')
            ->willReturn('/uploads/images/test_image.png');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    public function testCreateTeamMember(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'teammember';
        $_POST['email'] = 'testuser@example.com';
        $_POST['biography'] = 'Biography about teamMember.';
        $_POST['researcher_id'] = 1;

        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        // Check if the success message is present
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(201, http_response_code());

        // Ensure that the member has been created and the id is correct
        $this->assertEquals('Team member created successfully.', $response['message']);
        $this->assertArrayHasKey('id', $response);
        $this->assertNotEmpty($response['id']);
        self::$teamMemberId = $response['id'];
    }

    private function setRole($role): void
    {
        $_SESSION['role'] = $role;
    }

    public function testCreateTeamMemberNoName(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // No name
        $_POST['email'] = 'testmember@example.com';
        $_POST['biography'] = 'This is a biography';
        $_POST['researcher_id'] = 123;
        $_FILES['image_uri'] = ['tmp_name' => '/path/to/file', 'name' => 'image.jpg'];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateTeamMemberNoEmail(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Member';
        // No email
        $_POST['biography'] = 'This is a biography';
        $_POST['researcher_id'] = 123;
        $_FILES['image_uri'] = ['tmp_name' => '/path/to/file', 'name' => 'image.jpg'];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateTeamMemberNoBiography(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Member';
        $_POST['email'] = 'testmember@example.com';
        // No biography
        $_POST['researcher_id'] = 123;
        $_FILES['image_uri'] = ['tmp_name' => '/path/to/file', 'name' => 'image.jpg'];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateTeamMemberNoResearcherId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Member';
        $_POST['email'] = 'testmember@example.com';
        $_POST['biography'] = 'This is a biography';
        // No research_id
        $_FILES['image_uri'] = ['tmp_name' => '/path/to/file', 'name' => 'image.jpg'];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateTeamMemberNoImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Member';
        $_POST['email'] = 'testmember@example.com';
        $_POST['biography'] = 'This is a biography';
        $_POST['researcher_id'] = 123;
        // No image file

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateTeamMemberInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Member';
        $_POST['email'] = 'testmember@example.com';
        $_POST['biography'] = 'This is a biography';
        $_POST['researcher_id'] = 123;

        $_FILES['image_uri'] = [
            'name' => 'test_image.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();

        $this->teamMemberController->create();

        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);

    }


    #[Depends('testCreateTeamMember')]
    public function testGetTeamMember(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['id'] = self::$teamMemberId;

        ob_start();
        $this->teamMemberController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(self::$teamMemberId, $response['id']);
        $this->assertEquals('teammember', $response['name']);
        $this->assertEquals('testuser@example.com', $response['email']);
    }

    public function testInvalidTeamMemberId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET['id'] = 'invalid_id';

        ob_start();
        $this->teamMemberController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(400, http_response_code());

        $this->assertEquals('Invalid team member ID.', $response['error']);
    }

    public function testGetTeamMemberNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['id'] = 483427;

        ob_start();
        $this->teamMemberController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Team member not found.', $response['error']);
    }

    #[Depends('testCreateTeamMember')]
    public function testUpdateTeamMember(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = self::$teamMemberId;
        $_POST['name'] = 'Updated Name';
        $_POST['email'] = 'updated@example.com';
        $_POST['biography'] = 'Updated biography';
        $_POST['researcher_id'] = 1;
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');


        ob_start();
        $this->teamMemberController->update();
        $output = ob_get_clean();

        // Verify that the response is successful
        $response = json_decode($output, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals('Team member updated successfully.', $response['message']);
    }

    public function testUpdateInvalidId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 'invalid_id';
        $_POST['name'] = 'Updated Name';
        $_POST['email'] = 'updated@example.com';
        $_POST['biography'] = 'Updated biography';
        $_POST['researcher_id'] = 1;
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');


        ob_start();
        $this->teamMemberController->update();
        $output = ob_get_clean();

        // Verify that the response is successful
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid team member ID.', $response['error']);
    }

    public function testUpdateTeamMemberInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['name'] = 'Test Member';
        $_POST['email'] = 'testmember@example.com';
        $_POST['biography'] = 'This is a biography';
        $_POST['researcher_id'] = 123;
        $_FILES['image_uri'] = [
            'name' => 'test_image',
            'type' => 'image',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
    }

    public function testUpdateTeamMemberNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 483427;
        $_POST['email'] = 'testmember@example.com';
        $_POST['biography'] = 'This is a biography';
        $_POST['researcher_id'] = 123;
        $_FILES['image_uri'] = [
            'name' => 'test_image',
            'type' => 'image',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Team member not found.', $response['error']);
    }

    #[Depends('testCreateTeamMember')]
    public function testListTeamMember(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $filterData = ['name' => 'teammember'];

        ob_start();
        $this->teamMemberController->method('createFilterFromRequest')
            ->willReturn(TeamMemberFilter::fromArray($filterData));
        $this->teamMemberController->list();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue(isset($response['total_count']) && $response['total_count'] > 0);
        $this->assertArrayHasKey('name', $response['data'][1]);
        $this->assertEquals('teammember', $response['data'][1]['name']);
    }

    #[Depends('testCreateTeamMember')]
    #[Depends('testListTeamMember')]
    public function testDeleteTeamMember(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = self::$teamMemberId;

        ob_start();
        $this->teamMemberController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(200, http_response_code());
    }

    public function testDeleteInvalidTeamMemberId(): void
    {
        // Simulate a DELETE request to remove a team member with an invalid ID
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 'invalid-id';

        ob_start();
        $this->teamMemberController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        // Verify that the status code is 400
        $this->assertEquals(400, http_response_code());

        // Verify that the response contains the expected error message
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid team member ID.', $response['error']);
    }

    public function testDeleteUserNotFound(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 32849023;

        // Capture the output
        ob_start();
        $this->teamMemberController->delete();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Team member not found.', $response['error']);
    }

    public function testCreateTeamMemberInvalidImageType(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'Test Member';
        $_POST['email'] = 'testmember@example.com';
        $_POST['biography'] = 'This is a biography';
        $_POST['researcher_id'] = 123;

        $_FILES['image_uri'] = [
            'name' => 'document.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $this->setRole('admin');

        ob_start();
        $this->teamMemberController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid image file format. Only JPEG and PNG are allowed.', $response['error']);
    }
}
