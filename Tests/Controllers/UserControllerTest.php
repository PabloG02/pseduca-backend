<?php

namespace Tests\Controllers;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../loadconfig.php';

use App\Controllers\UserController;
use App\Filters\UserFilter;
use App\Services\UserService;
use Core\DIContainer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    private UserController $userController;

    protected function setUp(): void
    {
        // Set up the database connection and any other necessary configuration
        // This is where you might set up a mock database or use a test database
        $userService = DIContainer::resolve(UserService::class);
        $this->userController = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$userService])
            ->onlyMethods(['hasRole', 'createFilterFromRequest'])
            ->getMock();

        // getallheaders() is not available in PHPUnit, so we mock it
        $this->userController->method('hasRole')
            ->willReturn(true);
    }

    protected function tearDown(): void
    {
        $_POST = [];
    }

    public function testCreateUser(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'testuser';
        $_POST['password'] = 'securepassword';
        $_POST['email'] = 'test@example.com';
        $_POST['name'] = 'Test User';

        // Capture the output
        ob_start();
        $this->userController->create();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(201, http_response_code());
    }

    public function testCreateUserNoUsername(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // No username
        $_POST['password'] = 'securepassword';
        $_POST['email'] = 'test@example.com';
        $_POST['name'] = 'Test User';

        // Capture the output
        ob_start();
        $this->userController->create();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('All fields are required.', $response['error']);
    }

    public function testCreateUserNoPassword(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'testuser';
        // No password
        $_POST['email'] = 'test@example.com';
        $_POST['name'] = 'Test User';

        // Capture the output
        ob_start();
        $this->userController->create();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('All fields are required.', $response['error']);
    }

    public function testCreateUserNoEmail(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'testuser';
        $_POST['password'] = 'securepassword';
        // No email
        $_POST['name'] = 'Test User';

        // Capture the output
        ob_start();
        $this->userController->create();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('All fields are required.', $response['error']);
    }

    public function testCreateUserInvalidEmail(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'testuser';
        $_POST['password'] = 'securepassword';
        $_POST['email'] = 'invalidemail';
        $_POST['name'] = 'Test User';

        // Capture the output
        ob_start();
        $this->userController->create();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid email.', $response['error']);
    }

    public function testCreateUserNoName(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'testuser';
        $_POST['password'] = 'securepassword';
        $_POST['email'] = 'test@example.com';
        // No name

        // Capture the output
        ob_start();
        $this->userController->create();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('All fields are required.', $response['error']);
    }

    #[Depends('testCreateUser')]
    public function testGetUser(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['username'] = 'testuser';

        // Capture the output
        ob_start();
        $this->userController->get();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals(200, http_response_code());
        $this->assertArrayNotHasKey('password', $response);
        $this->assertEquals('testuser', $response['username']);
        $this->assertEquals('test@example.com', $response['email']);
    }

    public function testGetUserNoUsername(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // No username

        // Capture the output
        ob_start();
        $this->userController->get();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Username is required.', $response['error']);
    }

    public function testGetUserNotFound(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['username'] = 'aabbcc';

        // Capture the output
        ob_start();
        $this->userController->get();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(404, http_response_code());
    }

    #[Depends('testCreateUser')]
    public function testUpdateUserEmail(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['username'] = 'testuser';
        $_POST['email'] = 'test2@example.com';

        // Capture the output
        ob_start();
        $this->userController->update();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(200, http_response_code());
    }

    public function testUpdateUserNoUsername(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        // No username
        $_POST['email'] = 'test2@example.com';

        // Capture the output
        ob_start();
        $this->userController->update();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Username is required.', $response['error']);
    }

    public function testUpdateUserNotFound(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['username'] = 'aabbcc';
        $_POST['email'] = 'test123@example.com';

        // Capture the output
        ob_start();
        $this->userController->update();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('User not found.', $response['error']);
    }

    #[Depends('testCreateUser')]
    public function testList(): void
    {
        // Simulate the JSON input for the filter
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $filterData = ['username' => 'testuser'];

        // Capture the output
        ob_start();
        $this->userController->method('createFilterFromRequest')
            ->willReturn(UserFilter::fromArray($filterData));
        $this->userController->list();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Verificar la estructura principal
        $this->assertTrue($response['total_count'] > 0);
        $this->assertArrayHasKey('username', $response['data'][0]);
    }

    #[Depends('testCreateUser')]
    #[Depends('testUpdateUserEmail')]
    #[Depends('testList')]
    public function testDeleteUser(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['username'] = 'testuser';

        // Capture the output
        ob_start();
        $this->userController->delete();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(200, http_response_code());
    }

    public function testDeleteUserNoUsername(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        // No username

        // Capture the output
        ob_start();
        $this->userController->delete();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Username is required.', $response['error']);
    }

    public function testDeleteUserNotFound(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['username'] = 'aabbcc';

        // Capture the output
        ob_start();
        $this->userController->delete();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(404, http_response_code());
    }
}