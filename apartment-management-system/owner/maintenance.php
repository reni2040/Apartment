<?php
$config = include __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

// Get user's flat information
$userId = $_SESSION['user_id'];
$flatInfo = null;
$maintenanceRecords = [];

// Get user's flat
$stmt = $pdo->prepare("SELECT f.* FROM flats f JOIN users u ON f.id = u.flat_id WHERE u.id = ?");
$stmt->execute([$userId]);
$flatInfo = $stmt->fetch();

if ($flatInfo) {
    // Get all maintenance records for this flat
    $stmt = $pdo->prepare("SELECT * FROM maintenance_fees WHERE flat_id = ? ORDER BY month_year DESC");
    $stmt->execute([$flatInfo['id']]);
    $maintenanceRecords = $stmt->fetchAll();
}
?>
                <!-- Maintenance Overview -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        Maintenance Fee Management
                        <?php if ($flatInfo): ?>
                            <span class="ml-3 px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs rounded">
                                Flat #<?= htmlspecialchars($flatInfo['flat_number']) ?>
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        View and pay your maintenance fees
                    </p>
                </div>
                
                <?php if (!$flatInfo): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                        No flat assigned to your account. Please contact the administrator.
                    </div>
                <?php else: ?>
                    <!-- Current Maintenance -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Current Month Maintenance</h2>
                        <?php
                        $currentMonth = date('Y-m');
                        $currentMaintenance = null;
                        foreach ($maintenanceRecords as $record) {
                            if ($record['month_year'] === $currentMonth) {
                                $currentMaintenance = $record;
                                break;
                            }
                        }
                        
                        if ($currentMaintenance): ?>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Amount Due</p>
                                        <p class="text-2xl font-bold text-gray-900">₹<?= number_format($currentMaintenance['amount'], 2) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Amount Paid</p>
                                        <p class="text-2xl font-bold text-gray-900">₹<?= number_format($currentMaintenance['paid_amount'], 2) ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <span class="px-3 py-1 
                                        <?= $currentMaintenance['status'] === 'paid' ? 'bg-green-100 text-green-800' :
                                          ($currentMaintenance['status'] === 'overdue' ? 'bg-red-100 text-red-800' :
                                           ($currentMaintenance['status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) ?>">
                                        <?= ucfirst($currentMaintenance['status']) ?>
                                    </span>
                                    <?php if ($currentMaintenance['status'] === 'overdue'): ?>
                                        <span class="ml-3 text-sm text-red-600">
                                            Overdue since <?= date('M d, Y', strtotime($currentMaintenance['due_date'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">
                                        Due Date: <?= date('M d, Y', strtotime($currentMaintenance['due_date'])) ?>
                                    </p>
                                    <?php if ($currentMaintenance['special_charges'] > 0): ?>
                                        <p class="text-sm text-gray-600">
                                            Includes special charges: ₹<?= number_format($currentMaintenance['special_charges'], 2) ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($currentMaintenance['late_fee'] > 0): ?>
                                        <p class="text-sm text-gray-600">
                                            Late fee applied: ₹<?= number_format($currentMaintenance['late_fee'], 2) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <p class="text-gray-500">No maintenance record found for the current month.</p>
                                <p class="text-sm text-gray-600">The administrator may not have set up the maintenance fee for this month yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Payment Section -->
                    <div id="pay" class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Pay Maintenance Fee</h2>
                        <?php if ($currentMaintenance && $currentMaintenance['status'] !== 'paid'): ?>
                            <form action="process_payment.php" method="POST" class="space-y-4">
                                <input type="hidden" name="maintenance_fee_id" value="<?= $currentMaintenance['id'] ?>">
                                
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Amount to Pay (₹)
                                    </label>
                                    <input type="number" name="amount" id="amount" 
                                        value="<?= number_format($currentMaintenance['amount'] - $currentMaintenance['paid_amount'], 2) ?>" 
                                        min="0.01" step="0.01" required
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <p class="mt-1 text-xs text-gray-500">
                                        Maximum: ₹<?= number_format($currentMaintenance['amount'] - $currentMaintenance['paid_amount'], 2) ?>
                                    </p>
                                </div>
                                
                                <div>
                                    <button type="submit"
                                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                                        Proceed to Payment
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <?php if ($currentMaintenance && $currentMaintenance['status'] === 'paid'): ?>
                                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded" role="alert">
                                    Your maintenance fee for this month has been paid in full!
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Payment History -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Payment History</h2>
                        <?php if (!empty($maintenanceRecords)): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Due</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php foreach ($maintenanceRecords as $record): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?= date('M Y', strtotime($record['month_year'] . '-01')) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ₹<?= number_format($record['amount'], 2) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ₹<?= number_format($record['paid_amount'], 2) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <span class="px-2 py-0.5 
                                                        <?= $record['status'] === 'paid' ? 'bg-green-100 text-green-800' :
                                                          ($record['status'] === 'overdue' ? 'bg-red-100 text-red-800' :
                                                           ($record['status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) ?>">
                                                        <?= ucfirst($record['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                                    <a href="download_receipt.php?maintenance_fee_id=<?= $record['id'] ?>" 
                                                       class="font-medium text-indigo-600 hover:text-indigo-500">
                                                        Download Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No payment history found.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>