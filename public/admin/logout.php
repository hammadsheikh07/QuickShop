<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/includes/admin-helpers.php';

use App\Config\Database;
use App\Repositories\AdminRepository;
use App\Services\AuthService;

$authService = new AuthService(new AdminRepository(Database::getConnection()));
$authService->logout();

header('Location: login.php');
exit;

