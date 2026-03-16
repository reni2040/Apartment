<?php
// Quick DB Test - upload this to check your database connection
// Access this file directly: domain.com/test-db.php

header('Content-Type: text/plain');

echo "=== Database Connection Test ===\n\n";

echo "1. PHP Version: " . PHP_VERSION . "\n\n";

echo "2. PDO MySQL available: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n\n";

echo "3. Checking Cloudways Environment Variables:\n";
echo "   DB_HOST: " . (getenv('DB_HOST') ?: 'Not set') . "\n";
echo "   DB_NAME: " . (getenv('DB_NAME') ?: 'Not set') . "\n";
echo "   DB_USER: " . (getenv('DB_USER') ?: 'Not set') . "\n";
echo "   DB_PORT: " . (getenv('DB_PORT') ?: 'Not set') . "\n\n";

echo "4. Trying different connection methods...\n\n";

$tested = [];

// Try Cloudways env vars first
if (getenv('DB_HOST')) {
    echo "Testing with Cloudways env variables...\n";
    try {
        $dsn = "mysql:host=" . getenv('DB_HOST') . ";port=" . (getenv('DB_PORT') ?: '3306') . ";dbname=" . getenv('DB_NAME');
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "✅ SUCCESS with Cloudways env vars!\n";
        echo "   Host: " . getenv('DB_HOST') . "\n";
        echo "   Database: " . getenv('DB_NAME') . "\n";
        $tested[] = 'cloudways';
    } catch (PDOException $e) {
        echo "❌ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Try localhost
echo "Testing with localhost:3306...\n";
try {
    $pdo = new PDO("mysql:host=localhost;port=3306", 'root', '', [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ SUCCESS with localhost!\n";
    $tested[] = 'localhost';
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Try 127.0.0.1
echo "Testing with 127.0.0.1:3306...\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', '', [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ SUCCESS with 127.0.0.1!\n";
    $tested[] = '127.0.0.1';
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Try socket
echo "Testing with socket...\n";
try {
    $pdo = new PDO("mysql:unix_socket=/tmp/mysql.sock", 'root', '', [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ SUCCESS with socket!\n";
    $tested[] = 'socket';
} catch (PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Summary ===\n";
if (empty($tested)) {
    echo "❌ No connection methods worked.\n";
    echo "Possible reasons:\n";
    echo "  - MySQL service is not running\n";
    echo "  - Wrong hostname for Cloudways\n";
    echo "  - Database not created in Cloudways\n";
} else {
    echo "✅ Working methods: " . implode(', ', $tested) . "\n";
}
?>
