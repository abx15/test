<?php
require_once '../config/config.php';
require_once '../classes/AdminUser.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /admin/');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $user = new AdminUser($db);
        
        if ($user->login($username, $password)) {
            $_SESSION['admin_id'] = $user->id;
            $_SESSION['admin_username'] = $user->username;
            $_SESSION['admin_role'] = $user->role;
            
            header('Location: /admin/');
            exit();
        } else {
            $error_message = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#d97706',
                        secondary: '#92400e',
                        accent: '#fbbf24',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8">
        <div class="text-center">
            <a href="/" class="text-3xl font-bold text-primary"><?php echo SITE_NAME; ?></a>
            <h2 class="mt-6 text-2xl font-bold text-gray-900">Admin Login</h2>
            <p class="mt-2 text-gray-600">Sign in to manage your restaurant</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-8">
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username or Email
                    </label>
                    <div class="relative">
                        <input type="text" id="username" name="username" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                               value="<?php echo htmlspecialchars($username ?? ''); ?>">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-primary text-white py-3 px-4 rounded-lg font-semibold hover:bg-secondary transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="/" class="text-primary hover:text-secondary transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to Website
                </a>
            </div>
        </div>
        
        <div class="text-center text-sm text-gray-500">
            <p>Default login: admin / admin123</p>
        </div>
    </div>
</body>
</html>
