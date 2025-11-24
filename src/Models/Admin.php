<?php

namespace App\Models;

class Admin
{
    private int $id;
    private string $username;
    private string $passwordHash;

    public function __construct(
        int $id,
        string $username,
        string $passwordHash
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }
}

