<?php

namespace App\Services;

use App\Repositories\AdminRepository;
use App\Models\Admin;

class AuthService
{
    private AdminRepository $adminRepo;

    public function __construct(AdminRepository $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }

    public function login(string $username, string $password): ?Admin
    {
        $admin = $this->adminRepo->findByUsername($username);
        
        if (!$admin) {
            return null;
        }

        if (!$admin->verifyPassword($password)) {
            return null;
        }

        return $admin;
    }

    public function isAuthenticated(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_id']);
    }

    public function getCurrentAdmin(): ?Admin
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $this->adminRepo->findById($_SESSION['admin_id']);
    }

    public function setSession(Admin $admin): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_id'] = $admin->getId();
        $_SESSION['admin_username'] = $admin->getUsername();
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
    }
}

