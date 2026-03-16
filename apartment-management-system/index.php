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

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$user_role = $logged_in ? $_SESSION['user_role'] : null;
$user_id = $logged_in ? $_SESSION['user_id'] : null;

// If not logged in, redirect to login
if (!$logged_in && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'install/index.php') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@4.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-xl">
            <?php if (isset($_GET['installed']) && $_GET['installed'] == 1): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                    Installation successful! You can now <a href="login.php" class="font-medium underline">login</a>.
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="text-center mb-6">
                    <img class="mx-auto h-12 w-auto" src="https://img.icons8.com/color/96/000000/apartment.png" alt="Apartment">
                    <h2 class="mt-4 text-2xl font-bold text-gray-900">
                        <?= htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') ?>
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Welcome to the society management portal
                    </p>
                </div>
                
                <?php if (!$logged_in): ?>
                    <form action="login.php" method="POST" class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" name="email" id="email" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
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
                        
                        <p class="text-center text-xs text-gray-500">
                            © <?= date('Y') ?> <?= htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') ?>. All rights reserved.
                        </p>
                    </form>
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-green-600">You are logged in as <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?> (<?= htmlspecialchars($user_role) ?>)</p>
                        <div class="mt-6">
                            <?php if ($user_role === 'admin'): ?>
                                <a href="admin/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Admin Dashboard</a>
                            <?php elseif ($user_role === 'owner'): ?>
                                <a href="owner/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Owner Dashboard</a>
                            <?php elseif ($user_role === 'president'): ?>
                                <a href="president/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">President Dashboard</a>
                            <?php elseif ($user_role === 'secretary'): ?>
                                <a href="secretary/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Secretary Dashboard</a>
                            <?php elseif ($user_role === 'treasurer'): ?>
                                <a href="treasurer/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Treasurer Dashboard</a>
                            <?php else: ?>
                                <a href="executive/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Executive Dashboard</a>
                            <?php endif; ?>
                            <a href="logout.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Logout</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>