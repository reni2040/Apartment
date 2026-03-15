<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'president') {
    header('Location: ../../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>President Dashboard - <?= htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@4.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <div class="flex items-center space-x-3">
                    <img class="h-8 w-auto" src="https://img.icons8.com/color/96/000000/apartment.png" alt="Apartment">
                    <span class="text-xl font-bold text-gray-900">President Portal</span>
                </div>
            </div>
            <nav class="mt-6 space-y-2">
                <a href="dashboard.php" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                    <img class="h-4 w-4 mr-3" src="https://img.icons8.com/ios-filled/50/000000/dashboard-layout.png" alt="Dashboard">
                    Dashboard
                </a>
                <a href="reports.php" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                    <img class="h-4 w-4 mr-3" src="https://img.icons8.com/ios-filled/50/000000/chart.png" alt="Reports">
                    Reports
                </a>
                <a href="notices.php" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                    <img class="h-4 w-4 mr-3" src="https://img.icons8.com/ios-filled/50/000000/announcement.png" alt="Notices">
                    Notices
                </a>
                <a href="complaints.php" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                    <img class="h-4 w-4 mr-3" src="https://img.icons8.com/ios-filled/50/000000/complaint.png" alt="Complaints">
                    Complaints
                </a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 p-6">
            <header class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Dashboard
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'President') ?>!
                </p>
            </header>