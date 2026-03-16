<?php
// Sample Configuration - EDIT THIS FILE with your Cloudways database details
// Then rename to config.php before uploading

return [
    'db' => [
        'host' => 'localhost',          // Change to your Cloudways MySQL hostname
        'port' => '3306',              // Usually 3306
        'name' => 'your_db_name',      // Your database name from Cloudways
        'user' => 'your_db_user',      // Your database username from Cloudways
        'pass' => 'your_db_password',  // Your database password from Cloudways
    ],
    'society' => [
        'name' => 'Your Society Name',
        'address' => '',
        'gst_number' => '',
        'invoice_prefix' => 'RCPT',
    ],
    'admin_email' => 'admin@society.com',
    'admin_password' => '',  // Will be set during first login
];
?>
