<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Core\Inject;

class RoleService
{
    #[Inject]
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function list(): array
    {
        return $this->roleRepository->findAll();
    }
}
