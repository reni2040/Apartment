<?php
$config = include __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

// Get user's flat information
$userId = $_SESSION['user_id'];
$flatInfo = null;
$maintenanceDue = 0;
$paidAmount = 0;
$pendingAmount = 0;
$overdueAmount = 0;
$latestInvoice = null;
$paymentHistory = [];

// Get user's flat
$stmt = $pdo->prepare("SELECT f.* FROM flats f JOIN users u ON f.id = u.flat_id WHERE u.id = ?");
$stmt->execute([$userId]);
$flatInfo = $stmt->fetch();

if ($flatInfo) {
    // Get current month maintenance
    $currentMonth = date('Y-m');
    $stmt = $pdo->prepare("SELECT * FROM maintenance_fees WHERE flat_id = ? AND month_year = ?");
    $stmt->execute([$flatInfo['id'], $currentMonth]);
    $currentMaintenance = $stmt->fetch();
    
    if ($currentMaintenance) {
        $maintenanceDue = (float)$currentMaintenance['amount'];
        $paidAmount = (float)$currentMaintenance['paid_amount'];
        $pendingAmount = $maintenanceDue - $paidAmount;
        
        // Check if overdue
        $dueDate = new DateTime($currentMaintenance['due_date']);
        $today = new DateTime();
        if ($today > $dueDate && $currentMaintenance['status'] !== 'paid') {
            $overdueAmount = $pendingAmount;
        }
    }
    
    // Get latest invoice
    $stmt = $pdo->prepare("SELECT * FROM maintenance_fees WHERE flat_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$flatInfo['id']]);
    $latestInvoice = $stmt->fetch();
    
    // Get payment history (last 6 months)
    $stmt = $pdo->prepare("SELECT mf.*, p.payment_date, p.transaction_id FROM maintenance_fees mf LEFT JOIN payments p ON mf.id = p.maintenance_fee_id WHERE mf.flat_id = ? ORDER BY mf.month_year DESC LIMIT 6");
    $stmt->execute([$flatInfo['id']]);
    $paymentHistory = $stmt->fetchAll();
}
?>
                <!-- Welcome Section -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Owner') ?>!
                        <?php if ($flatInfo): ?>
                            <span class="ml-3 px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs rounded">
                                Flat #<?= htmlspecialchars($flatInfo['flat_number']) ?>
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Here's an overview of your society account
                    </p>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Maintenance Due -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Maintenance Due</p>
                                <p class="text-2xl font-bold text-gray-900">₹<?= number_format($pendingAmount, 2) ?></p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center bg-indigo-100 rounded-lg">
                                <img class="h-5 w-5" src="https://img.icons8.com/ios-filled/50/000000/coins.png" alt="Maintenance">
                            </div>
                        </div>
                        <?php if ($overdueAmount > 0): ?>
                            <div class="mt-2 px-3 py-1 bg-red-100 text-red-800 text-xs rounded">
                                Overdue: ₹<?= number_format($overdueAmount, 2) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Paid This Month -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Paid This Month</p>
                                <p class="text-2xl font-bold text-gray-900">₹<?= number_format($paidAmount, 2) ?></p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center bg-indigo-100 rounded-lg">
                                <img class="h-5 w-5" src="https://img.icons8.com/ios-filled/50/000000/coins.png" alt="Paid">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Next Due Date -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Next Due Date</p>
                                <p class="text-xl font-bold text-gray-900">
                                    <?php if ($currentMaintenance && isset($currentMaintenance['due_date'])): ?>
                                        <?= date('M d, Y', strtotime($currentMaintenance['due_date'])) ?>
                                    <?php else: ?>
                                        Not Set
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center bg-indigo-100 rounded-lg">
                                <img class="h-5 w-5" src="https://img.icons8.com/ios-filled/50/000000/calendar.png" alt="Calendar">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity & Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Payment Activity -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Activity</h2>
                        <?php if ($latestInvoice): ?>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b">
                                    <span class="text-sm font-medium text-gray-500">Latest Invoice</span>
                                    <span class="text-sm font-medium"><?= strtoupper(date('M Y', strtotime($latestInvoice['month_year'] . '-01'))) ?></span>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-600">Amount Due: ₹<?= number_format($latestInvoice['amount'], 2) ?></p>
                                    <p class="text-sm text-gray-600">Amount Paid: ₹<?= number_format($latestInvoice['paid_amount'], 2) ?></p>
                                    <p class="text-sm text-gray-600">Status: 
                                        <span class="px-2 py-0.5 
                                            <?= $latestInvoice['status'] === 'paid' ? 'bg-green-100 text-green-800' :
                                              ($latestInvoice['status'] === 'overdue' ? 'bg-red-100 text-red-800' :
                                               ($latestInvoice['status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) ?>">
                                            <?= ucfirst($latestInvoice['status']) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No maintenance records found.</p>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="maintenance.php" class="w-full flex items-center justify-center px-3 py-2 bg-indigo-600 text-white font-medium text-sm rounded-lg hover:bg-indigo-700 transition-colors">
                                View Maintenance Details
                            </a>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                        <div class="space-y-4">
                            <a href="maintenance.php#pay" class="w-full flex items-center justify-center px-3 py-2 bg-indigo-600 text-white font-medium text-sm rounded-lg hover:bg-indigo-700 transition-colors">
                                Pay Maintenance Fee
                            </a>
                            <a href="complaints.php" class="w-full flex items-center justify-center px-3 py-2 bg-white border border-gray-300 text-gray-700 font-medium text-sm rounded-lg hover:bg-gray-50 transition-colors">
                                Raise a Complaint
                            </a>
                            <a href="notices.php" class="w-full flex items-center justify-center px-3 py-2 bg-white border border-gray-300 text-gray-700 font-medium text-sm rounded-lg hover:bg-gray-50 transition-colors">
                                View Notices
                            </a>
                            <a href="reports.php" class="w-full flex items-center justify-center px-3 py-2 bg-indigo-50 text-indigo-600 font-medium text-sm rounded-lg hover:bg-indigo-100 transition-colors">
                                Download Reports
                            </a>
                        </div>
                    </div>
                </div>
<?php include __DIR__ . '/includes/footer.php'; ?>