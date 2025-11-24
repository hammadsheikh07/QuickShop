<?php

use PHPUnit\Framework\TestCase;
use App\Models\Admin;

class AdminTest extends TestCase
{
    public function test_admin_creation()
    {
        $admin = new Admin(1, 'admin', password_hash('password123', PASSWORD_DEFAULT));
        
        $this->assertEquals(1, $admin->getId());
        $this->assertEquals('admin', $admin->getUsername());
        $this->assertNotEmpty($admin->getPasswordHash());
    }

    public function test_password_verification_success()
    {
        $password = 'password123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $admin = new Admin(1, 'admin', $hash);
        
        $this->assertTrue($admin->verifyPassword($password));
    }

    public function test_password_verification_failure()
    {
        $password = 'password123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $admin = new Admin(1, 'admin', $hash);
        
        $this->assertFalse($admin->verifyPassword('wrongpassword'));
    }
}

