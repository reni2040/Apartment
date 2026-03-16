<?php
// Apartment Management System Installer
// Step-based installer like WordPress

session_start();

define('STEPS', ['welcome', 'database', 'admin', 'complete']);

// Check if already installed
if (file_exists(__DIR__ . '/../config.php') && !isset($_GET['reinstall'])) {
    header('Location: ../index.php');
    exit;
}

// Debug function
function get_db_diagnostics() {
    $info = [];
    $info['php_version'] = PHP_VERSION;
    $info['pdo_mysql'] = extension_loaded('pdo_mysql') ? 'Yes' : 'No';
    $info['mysqlnd'] = extension_loaded('mysqlnd') ? 'Yes' : 'No';
    $info['cloudways_vars'] = [
        'DB_HOST' => getenv('DB_HOST') ?: 'Not set',
        'DB_NAME' => getenv('DB_NAME') ?: 'Not set', 
        'DB_USER' => getenv('DB_USER') ?: 'Not set',
    ];
    return $info;
}

$current_step = $_GET['step'] ?? 'welcome';
$step_index = array_search($current_step, STEPS);

if ($step_index === false) {
    $current_step = 'welcome';
    $step_index = 0;
}

$errors = [];
$success = [];
$diagnostics = get_db_diagnostics();

// Process each step
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'test_db') {
        $db_host = $_POST['db_host'] ?? '';
        $db_port = $_POST['db_port'] ?? '3306';
        $db_name = $_POST['db_name'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';
        
        // Auto-detect Cloudways credentials if empty
        if (empty($db_host) && getenv('DB_HOST')) {
            $db_host = getenv('DB_HOST');
            $db_port = getenv('DB_PORT') ?: '3306';
            $db_name = getenv('DB_NAME');
            $db_user = getenv('DB_USER');
            $db_pass = getenv('DB_PASSWORD');
        }
        
        if (empty($db_host) || empty($db_name) || empty($db_user)) {
            $errors[] = 'Please fill in all required fields';
        } else {
            try {
                $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name";
                $pdo = new PDO($dsn, $db_user, $db_pass, [
                    PDO::ATTR_TIMEOUT => 10,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $success[] = 'Database connection successful!';
                $_SESSION['db_config'] = compact('db_host', 'db_port', 'db_name', 'db_user', 'db_pass');
            } catch (PDOException $e) {
                $errors[] = 'Connection failed: ' . $e->getMessage();
                $errors[] = 'Host: ' . $db_host . ' | Port: ' . $db_port . ' | DB: ' . $db_name;
            }
        }
    }
    
    if ($action === 'install') {
        $db_host = $_SESSION['db_config']['db_host'] ?? '';
        $db_port = $_SESSION['db_config']['db_port'] ?? '3306';
        $db_name = $_SESSION['db_config']['db_name'] ?? '';
        $db_user = $_SESSION['db_config']['db_user'] ?? '';
        $db_pass = $_SESSION['db_config']['db_pass'] ?? '';
        
        $admin_email = $_POST['admin_email'] ?? '';
        $admin_password = $_POST['admin_password'] ?? '';
        $society_name = $_POST['society_name'] ?? '';
        
        if (empty($admin_email) || empty($admin_password) || empty($society_name)) {
            $errors[] = 'Please fill in all required fields';
        } else {
            try {
                $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sql = file_get_contents(__DIR__ . '/schema.sql');
                $statements = preg_split('/;[\r\n]+/', $sql);
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
                
                $config = "<?php\n";
                $config .= "return [\n";
                $config .= "    'db' => [\n";
                $config .= "        'host' => '$db_host',\n";
                $config .= "        'port' => '$db_port',\n";
                $config .= "        'name' => '$db_name',\n";
                $config .= "        'user' => '$db_user',\n";
                $config .= "        'pass' => '$db_pass',\n";
                $config .= "    ],\n";
                $config .= "    'society' => [\n";
                $config .= "        'name' => '$society_name',\n";
                $config .= "        'address' => '',\n";
                $config .= "        'gst_number' => '',\n";
                $config .= "        'invoice_prefix' => 'RCPT',\n";
                $config .= "    ],\n";
                $config .= "    'admin_email' => '$admin_email',\n";
                $config .= "    'admin_password' => '" . password_hash($admin_password, PASSWORD_DEFAULT) . "',\n";
                $config .= "];\n";
                $config .= "?>";
                
                file_put_contents(__DIR__ . '/../config.php', $config);
                
                header('Location: ?step=complete');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Installation failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Apartment Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto py-12 px-4">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Apartment Management System</h1>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <?php foreach (STEPS as $i => $step): ?>
                    <?php $is_active = $step === $current_step; ?>
                    <?php $is_complete = array_search($current_step, STEPS) > $i; ?>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium 
                            <?= $is_active ? 'bg-blue-600 text-white' : ($is_complete ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') ?>">
                            <?= $is_complete ? '✓' : ($i + 1) ?>
                        </div>
                        <span class="text-xs mt-1 text-gray-600 capitalize"><?= $step ?></span>
                    </div>
                    <?php if ($i < count(STEPS) - 1): ?>
                        <div class="flex-1 h-1 mx-2 <?= $is_complete ? 'bg-green-500' : 'bg-gray-300' ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- System Info -->
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h3 class="font-medium text-yellow-800 mb-2">🔧 System Diagnostics</h3>
                <div class="text-xs text-yellow-700 space-y-1">
                    <p>PHP Version: <?= $diagnostics['php_version'] ?></p>
                    <p>PDO MySQL: <?= $diagnostics['pdo_mysql'] ?></p>
                    <p>CloudWays DB Host Env: <?= $diagnostics['cloudways_vars']['DB_HOST'] ?></p>
                </div>
            </div>

            <!-- Errors -->
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Success -->
            <?php if (!empty($success)): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <?= htmlspecialchars($success[0]) ?>
                </div>
            <?php endif; ?>

            <!-- Welcome Step -->
            <?php if ($current_step === 'welcome'): ?>
                <div class="text-center">
                    <h2 class="text-xl font-semibold mb-4">Welcome to Installation</h2>
                    <p class="text-gray-600 mb-6">This installer will guide you through setting up your Apartment Management System.</p>
                    
                    <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="font-medium mb-2">What you'll need:</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Database name</li>
                            <li>• Database username & password</li>
                            <li>• Your society name</li>
                            <li>• Admin account details</li>
                        </ul>
                    </div>
                    
                    <a href="?step=database" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                        Let's Go →
                    </a>
                </div>

            <!-- Database Step -->
            <?php elseif ($current_step === 'database'): ?>
                <?php 
                // Pre-fill from Cloudways env vars
                $default_host = getenv('DB_HOST') ?: 'localhost';
                $default_port = getenv('DB_PORT') ?: '3306';
                $default_name = getenv('DB_NAME') ?: '';
                $default_user = getenv('DB_USER') ?: '';
                ?>
                <h2 class="text-xl font-semibold mb-6">Database Configuration</h2>
                <?php if (getenv('DB_HOST')): ?>
                    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">
                        ✅ Cloudways credentials detected! Just enter database name if needed.
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="test_db">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Database Host *</label>
                                <input type="text" name="db_host" id="db_host" value="<?= $default_host ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                                <input type="text" name="db_port" value="<?= $default_port ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Database Name *</label>
                            <input type="text" name="db_name" id="db_name" value="<?= $default_name ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                            <input type="text" name="db_user" id="db_user" value="<?= $default_user ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="db_pass" id="db_pass"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="mt-6 flex gap-3">
                        <a href="?step=welcome" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">← Back</a>
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Test Connection
                        </button>
                    </div>
                </form>

            <!-- Admin Step -->
            <?php elseif ($current_step === 'admin'): ?>
                <h2 class="text-xl font-semibold mb-6">Site & Admin Account</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="install">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Society Name *</label>
                            <input type="text" name="society_name" required placeholder="e.g. Sunset Apartments"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email *</label>
                            <input type="email" name="admin_email" required placeholder="admin@example.com"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Admin Password *</label>
                            <input type="password" name="admin_password" required minlength="6" placeholder="Minimum 6 characters"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="mt-6 flex gap-3">
                        <a href="?step=database" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">← Back</a>
                        <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-medium">
                            Install Now
                        </button>
                    </div>
                </form>

            <!-- Complete Step -->
            <?php elseif ($current_step === 'complete'): ?>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold mb-2">Installation Complete!</h2>
                    <p class="text-gray-600 mb-6">Your Apartment Management System is ready.</p>
                    
                    <div class="bg-gray-50 rounded-lg p-4 text-left mb-6">
                        <p class="text-sm text-gray-600 mb-2">Login at:</p>
                        <a href="../login.php" class="text-blue-600 font-medium hover:underline">../login.php</a>
                    </div>
                    
                    <a href="../login.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700">
                        Log In Now →
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <p class="text-center text-gray-500 text-sm mt-8">
            Apartment Management System v1.0
        </p>
    </div>
</body>
</html>
