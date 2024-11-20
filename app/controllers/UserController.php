<?php

namespace App\Controllers;

use App\Entities\User;
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
}