<?php

require_once __DIR__ . '/../../public/includes/session-init.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/includes/helpers.php';

use App\Config\Database;
use App\Repositories\AdminRepository;
use App\Services\AuthService;

$authService = new AuthService(new AdminRepository(Database::getConnection()));
if ($authService->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $admin = $authService->login($username, $password);
        if ($admin) {
            $authService->setSession($admin);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - QuickShop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-box">
            <h1>Admin Login</h1>
            <p class="admin-login-subtitle">QuickShop Administration</p>
            
            <?php if ($error): ?>
                <div class="admin-error-message">
                    <?php echo escapeHtml($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="admin-login-form">
                <div class="admin-form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                        value="<?php echo isset($_POST['username']) ? escapeHtml($_POST['username']) : ''; ?>"
                    >
                </div>

                <div class="admin-form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                    >
                </div>

                <button type="submit" class="admin-login-button">Sign In</button>
            </form>

            <div class="admin-login-footer">
                <a href="../index.php">← Back to Store</a>
            </div>
        </div>
    </div>
</body>
</html>

