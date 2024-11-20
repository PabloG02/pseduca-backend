<?php

namespace App\Services;

use App\Entities\User;
use App\Repositories\UserRepository;
use Core\Inject;

class UserService
{
    #[Inject]
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create(User $user): void
    {
        $this->userRepository->insert($user);
    }
}
