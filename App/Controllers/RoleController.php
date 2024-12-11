<?php

namespace App\Controllers;

use App\Services\RoleService;
use Core\Inject;

class RoleController extends BaseController
{
    #[Inject]
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function list(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $roles = $this->roleService->list();

        http_response_code(200);
        echo json_encode($roles);
    }
}