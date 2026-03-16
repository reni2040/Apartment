<?php
session_start();

// Check if installed
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: install/');
    exit;
}

// Load configuration
$config = include __DIR__ . '/config.php';

// Database connection
$host = $config['db']['host'];
$port = $config['db']['port'] ?? '3306';
$dbname = $config['db']['name'];
$username = $config['db']['user'];
$password = $config['db']['pass'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    if (empty($errors)) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, username, email, password, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Check if user is active
            if ($user['status'] === 'blocked') {
                $errors[] = 'Your account has been blocked. Please contact administrator.';
            } else {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'owner':
                        header('Location: owner/dashboard.php');
                        break;
                    case 'president':
                        header('Location: president/dashboard.php');
                        break;
                    case 'secretary':
                        header('Location: secretary/dashboard.php');
                        break;
                    case 'treasurer':
                        header('Location: treasurer/dashboard.php');
                        break;
                    default:
                        header('Location: executive/dashboard.php');
                        break;
                }
                exit;
            }
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@4.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-xl">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="text-center mb-6">
                    <img class="mx-auto h-12 w-auto" src="https://img.icons8.com/color/96/000000/apartment.png" alt="Apartment">
                    <h2 class="mt-4 text-2xl font-bold text-gray-900">
                        <?= htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') ?>
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Please sign in to continue
                    </p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input type="email" name="email" id="email" required
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input type="password" name="password" id="password" required
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="text-sm">
                            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                                Forgot password?
                            </a>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit"
                            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                            Sign in
                        </button>
                    </div>
                    
                    <p class="text-center text-xs text-gray-500 mt-6">
                        © <?= date('Y') ?> <?= htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') ?>. All rights reserved.
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>