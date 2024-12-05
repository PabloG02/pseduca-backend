<?php

namespace App\Controllers;

use App\Services\UserService;
use Core\DIContainer;
use Core\Jwt;
use Exception;

class BaseController
{
    protected function hasRole(string $role): bool
    {
        try {
            // keys to lowercase
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
            $auth_header = $headers['authorization'] ?? null;
            if (!$auth_header) {
                return false;
            }

            $token = explode(' ', $auth_header)[1] ?? null;
            if (!$token) {
                return false;
            }

            $payload = Jwt::verify($token);
            $username = $payload['sub'];
            $userService = DIContainer::resolve(UserService::class);
            $user = $userService->get($username);

            return in_array($role, $user->roles);
        } catch (Exception) {
            return false;
        }
    }
}