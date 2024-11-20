<?php

namespace App\Repositories;

use App\Entities\User;
use Core\Inject;
use PDO;

class UserRepository
{
    #[Inject]
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(User $user): void
    {
        $stmt = $this->db->prepare("INSERT INTO User (username, password, email, name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user->username, $user->password, $user->email, $user->name]);
    }
}
