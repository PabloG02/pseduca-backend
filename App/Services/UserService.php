<?php

namespace App\Services;

use App\Entities\User;
use App\Filters\UserFilter;
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
        // Add validation or business logic before creating
        $this->userRepository->insert($user);
    }

    public function update(User $user): void
    {
        // Add validation or business logic before updating
        $this->userRepository->update($user);
    }

    public function delete(string $username): void
    {
        $this->userRepository->delete($username);
    }

    public function get(string $username): ?User
    {
        return $this->userRepository->find($username);
    }

    public function list(?UserFilter $filter = null): array
    {
        return $this->userRepository->findAll($filter);
    }
}
