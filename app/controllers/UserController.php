<?php

namespace App\Controllers;

use App\Entities\User;
use App\Filters\UserFilter;
use App\Services\UserService;
use Core\Inject;
use PDOException;

class UserController
{
    #[Inject]
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function create(): void {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $name = filter_input(INPUT_POST, 'name');

        // Check for required fields
        if (!isset($username) || !isset($password) || !isset($email) || !isset($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required.']);
            return;
        }

        if ($email === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email.']);
            return;
        }

        try {
            $this->userService->create(new User($username, $password, $email, $name));
            http_response_code(201);
            echo json_encode(['message' => 'User created successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function update(): void
    {
        $username = filter_input(INPUT_POST, 'username');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $name = filter_input(INPUT_POST, 'name');
        $activated = filter_input(INPUT_POST, 'activated', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        // Check for required fields
        if (!isset($username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username is required.']);
            return;
        }

        if (isset($email) && $email === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email.']);
            return;
        }

        try {
            $user = $this->userService->get($username);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found.']);
                return;
            }

            // Update fields if provided
            if ($email !== null) {
                $user->email = $email;
            }
            if ($name !== null) {
                $user->name = $name;
            }
            if ($activated !== null) {
                $user->activated = $activated;
            }

            $this->userService->update($user);

            http_response_code(200);
            echo json_encode(['message' => 'User updated successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function delete(): void
    {
        $username = filter_input(INPUT_POST, 'username');

        if (!$username) {
            http_response_code(400);
            echo json_encode(['error' => 'Username is required.']);
            return;
        }

        try {
            $user = $this->userService->get($username);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found.']);
                return;
            }

            $this->userService->delete($username);

            http_response_code(200);
            echo json_encode(['message' => 'User deleted successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function get(): void
    {
        $username = filter_input(INPUT_GET, 'username');

        if (!$username) {
            http_response_code(400);
            echo json_encode(['error' => 'Username is required.']);
            return;
        }

        try {
            $user = $this->userService->get($username);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($user);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $users = $this->userService->list($filter);
            http_response_code(200);
            echo json_encode($users);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function createFilterFromRequest(): UserFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new UserFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return UserFilter::fromArray($data);
    }
}