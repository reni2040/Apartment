<?php
// Installer for Apartment Management System
// Step 1: Check if already installed
if (file_exists(__DIR__ . '/../config.php')) {
    header('Location: ../index.php');
    exit;
}

// Step 2: Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_password = $_POST['admin_password'] ?? '';
    $society_name = $_POST['society_name'] ?? '';

    // Basic validation
    $errors = [];
    if (empty($db_name)) $errors[] = 'Database name is required';
    if (empty($db_user)) $errors[] = 'Database user is required';
    if (empty($admin_email)) $errors[] = 'Admin email is required';
    if (empty($admin_password)) $errors[] = 'Admin password is required';
    if (empty($society_name)) $errors[] = 'Society name is required';

    if (empty($errors)) {
        // Try to connect to database
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // If we get here, the database exists or we can create it? 
            // Actually, we should try to create the database if it doesn't exist.
            // But for simplicity, we assume the database is created by the user via phpMyAdmin or similar.
            // Alternatively, we can try to create the database.
            // Let's try to create the database if it doesn't exist.
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
            $pdo->exec("USE `$db_name`");

            // Now create tables
            $sql = file_get_contents(__DIR__ . '/../install/schema.sql');
            if ($sql === false) {
                throw new Exception('Could not read schema file');
            }

            // Split SQL statements by semicolon followed by newline or end of string
            $statements = preg_split('/;[\r\n]+/', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            // Create config.php
            $config = "<?php\n";
            $config .= "return [\n";
            $config .= "    'db' => [\n";
            $config .= "        'host' => '$db_host',\n";
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

            if (file_put_contents(__DIR__ . '/../config.php', $config) === false) {
                throw new Exception('Could not write config file');
            }

            // Success
            header('Location: ../index.php?installed=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Apartment Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@4.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <div>
                <img class="mx-auto h-12 w-auto" src="https://img.icons8.com/color/96/000000/apartment.png" alt="Apartment">
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Install Apartment Management System
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Please fill in the details to install the system.
                </p>
            </div>
            <form class="mt-8 space-y-6" method="POST" action="">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <label for="society_name" class="block text-sm font-medium text-gray-700">
                        Society Name
                    </label>
                    <input type="text" name="society_name" id="society_name" required
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="db_host" class="block text-sm font-medium text-gray-700">
                            Database Host
                        </label>
                        <input type="text" name="db_host" id="db_host" value="localhost"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <div>
                        <label for="db_name" class="block text-sm font-medium text-gray-700">
                            Database Name
                        </label>
                        <input type="text" name="db_name" id="db_name" required
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="db_user" class="block text-sm font-medium text-gray-700">
                            Database Username
                        </label>
                        <input type="text" name="db_user" id="db_user" required
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <div>
                        <label for="db_pass" class="block text-sm font-medium text-gray-700">
                            Database Password
                        </label>
                        <input type="password" name="db_pass" id="db_pass"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <div class="space-y-4">
                    <label for="admin_email" class="block text-sm font-medium text-gray-700">
                        Admin Email
                    </label>
                    <input type="email" name="admin_email" id="admin_email" required
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <label for="admin_password" class="block text-sm font-medium text-gray-700">
                        Admin Password
                    </label>
                    <input type="password" name="admin_password" id="admin_password" required
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                </div>

                <div>
                    <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                        Install
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-xs text-gray-500">
                © <?= date('Y') ?> Apartment Management System. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>