<?php

namespace App\Repositories;

use PDO;
use App\Models\Admin;

class AdminRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?Admin
    {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Admin(
            $row['id'],
            $row['username'],
            $row['password_hash']
        );
    }

    public function findById(int $id): ?Admin
    {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Admin(
            $row['id'],
            $row['username'],
            $row['password_hash']
        );
    }
}

