<?php
$config = include __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

// Get statistics for president dashboard
$totalFlats = 0;
$occupiedFlats = 0;
$totalUsers = 0;
$totalMaintenanceDue = 0;
$totalMaintenanceCollected = 0;
$newComplaints = 0;
$pendingApprovals = 0;

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

// Get new complaints (open status)
$stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'open'");
$newComplaints = (int)$stmt->fetchColumn();

// Get pending approvals (events, facility bookings, etc.)
$stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE is_approved = FALSE");
$pendingApprovals = (int)$stmt->fetchColumn();
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
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Activity</h2>
                    <div class="space-y-4">
                        <!-- Complaints -->
                        <div class="border-t py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">New Complaints</span>
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded"><?= $newComplaints ?></span>
                            </div>
                            <?php if ($newComplaints > 0): ?>
                                <p class="mt-1 text-sm text-gray-500">You have <?= $newComplaints ?> new complaint(s) requiring attention.</p>
                            <?php else: ?>
                                <p class="mt-1 text-sm text-gray-500">No new complaints.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pending Approvals -->
                        <div class="border-t py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">Pending Approvals</span>
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded"><?= $pendingApprovals ?></span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">You have <?= $pendingApprovals ?> item(s) awaiting approval.</p>
                        </div>
                        
                        <!-- Maintenance Collection -->
                        <div class="border-t py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">Collection Rate</span>
                                <?php
                                $collectionRate = $totalMaintenanceCollected > 0 ? 
                                    min(100, round(($totalMaintenanceCollected / ($totalMaintenanceCollected + $totalMaintenanceDue)) * 100)) : 0;
                                ?>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded"><?= $collectionRate ?>%</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">₹<?= number_format($totalMaintenanceCollected, 2) ?> collected of ₹<?= number_format($totalMaintenanceCollected + $totalMaintenanceDue, 2) ?> total due</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="reports.php" class="bg-white rounded-lg shadow p-4 hover:bg-indigo-50 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-8 w-8" src="https://img.icons8.com/ios-filled/50/000000/chart.png" alt="Reports">
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-700">View Reports</p>
                                    <p class="text-xs text-gray-500 mt-1">Access society reports and analytics</p>
                                </div>
                            </div>
                        </a>
                        <a href="notices.php" class="bg-white rounded-lg shadow p-4 hover:bg-indigo-50 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-8 w-8" src="https://img.icons8.com/ios-filled/50/000000/announcement.png" alt="Notices">
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-700">Manage Notices</p>
                                    <p class="text-xs text-gray-500 mt-1">Post and manage society notices</p>
                                </div>
                            </div>
                        </a>
                        <a href="complaints.php" class="bg-white rounded-lg shadow p-4 hover:bg-indigo-50 hover:shadow-md transition-all">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img class="h-8 w-8" src="https://img.icons8.com/ios-filled/50/000000/complaint.png" alt="Complaints">
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-700">Handle Complaints</p>
                                    <p class="text-xs text-gray-500 mt-1">Review and resolve member complaints</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
<?php include __DIR__ . '/includes/footer.php'; ?>