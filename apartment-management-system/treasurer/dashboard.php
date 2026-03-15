<?php
$config = include __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

// Get statistics for treasurer dashboard
$totalFlats = 0;
$occupiedFlats = 0;
$totalUsers = 0;
$totalMaintenanceDue = 0;
$totalMaintenanceCollected = 0;
$collectionRate = 0;
$defaultersCount = 0;

// Get total flats
$stmt = $pdo->query("SELECT COUNT(*) FROM flats");
$totalFlats = (int)$stmt->fetchColumn();

// Get occupied flats
$stmt = $pdo->query("SELECT COUNT(*) FROM flats WHERE status = 'occupied'");
$occupiedFlats = (int)$stmt->fetchColumn();

// Get total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
$totalUsers = (int)$stmt->fetchColumn();

// Get total maintenance due (pending + partial)
$stmt = $pdo->query("SELECT SUM(amount - paid_amount) FROM maintenance_fees WHERE status IN ('pending', 'partial')");
$result = $stmt->fetch();
$totalMaintenanceDue = $result[0] ? (float)$result[0] : 0;

// Get total maintenance collected (sum of paid amounts)
$stmt = $pdo->query("SELECT SUM(paid_amount) FROM maintenance_fees");
$result = $stmt->fetch();
$totalMaintenanceCollected = $result[0] ? (float)$result[0] : 0;

// Calculate collection rate
if ($totalMaintenanceCollected + $totalMaintenanceDue > 0) {
    $collectionRate = min(100, round(($totalMaintenanceCollected / ($totalMaintenanceCollected + $totalMaintenanceDue)) * 100));
}

// Get defaulters count (overdue for more than 30 days)
$stmt = $pdo->query("SELECT COUNT(DISTINCT flat_id) FROM maintenance_fees WHERE status = 'overdue' AND due_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$defaultersCount = (int)$stmt->fetchColumn();
?>
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Flats</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $totalFlats ?></p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center bg-indigo-100 rounded-lg">
                                <img class="h-5 w-5" src="https://img.icons8.com/ios-filled/50/000000/home.png" alt="Flats">
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Occupied Flats</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $occupiedFlats ?></p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center bg-indigo-100 rounded-lg">
                                <img class="h-5 w-5" src="https://img.icons8.com/ios-filled/50/000000/home.png" alt="Occupied Flats">
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $totalUsers ?></p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center bg-indigo-100 rounded-lg">
                                <img class="h-5 w-5" src="https://img.icons8.com/ios-filled/50/000000/user-group.png" alt="Users">
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Maintenance Due</p>
                                <p class="text-2xl font-bold text-gray-900">₹<?= number_format($totalMaintenanceDue, 2) ?></p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center bg-indigo-100 rounded-lg">
                                <img class="h-5 w-5" src="https://img.icons8.com/ios-filled/50/000000/coins.png" alt="Maintenance">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Collection Overview -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Collection Overview</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Collected</p>
                            <p class="text-2xl font-bold text-gray-900">₹<?= number_format($totalMaintenanceCollected, 2) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Collection Rate</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $collectionRate ?>%</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="flex items-center">
                            <span class="px-3 py-1 text-xs 
                                <?= $collectionRate >= 90 ? 'bg-green-100 text-green-800' :
                                  ($collectionRate >= 75 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= $collectionRate ?>% Collection Rate
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Activity</h2>
                    <div class="space-y-4">
                        <!-- Defaulters -->
                        <div class="border-t py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">Defaulters (>30 days)</span>
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded"><?= $defaultersCount ?></span>
                            </div>
                            <?php if ($defaultersCount > 0): ?>
                                <p class="mt-1 text-sm text-gray-500">You have <?= $defaultersCount ?> flat(s) with overdue payments for more than 30 days.</p>
                            <?php else: ?>
                                <p class="mt-1 text-sm text-gray-500">No defaulters found.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Recent Payments -->
                        <div class="border-t py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">Recent Payments</span>
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs rounded">
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'success'");
                                    echo $stmt->fetchColumn();
                                    ?>
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Payments received in the last 7 days.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="payments.php" class="bg-white rounded-lg shadow p-4 hover:bg-indigo-50 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-8 w-8" src="https://img.icons8.com/ios-filled/50/000000/coins.png" alt="Payments">
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-700">Manage Payments</p>
                                    <p class="text-xs text-gray-500 mt-1">View, record, and track all payments</p>
                                </div>
                            </div>
                        </a>
                        <a href="reports.php" class="bg-white rounded-lg shadow p-4 hover:bg-indigo-50 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-8 w-8" src="https://img.icons8.com/ios-filled/50/000000/chart.png" alt="Reports">
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-700">Generate Reports</p>
                                    <p class="text-xs text-gray-500 mt-1">Create financial and collection reports</p>
                                </div>
                            </div>
                        </a>
                        <a href="accounts.php" class="bg-white rounded-lg shadow p-4 hover:bg-indigo-50 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-8 w-8" src="https://img.icons8.com/ios-filled/50/000000/calculator.png" alt="Accounts">
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-700">Manage Accounts</p>
                                    <p class="text-xs text-gray-500 mt-1">Handle society accounts and expenditures</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
<?php include __DIR__ . '/includes/footer.php'; ?>