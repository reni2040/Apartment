<?php
$config = include __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_maintenance_fee'])) {
        // Set global maintenance fee
        $amount = $_POST['amount'] ?? 0;
        $due_date_day = $_POST['due_date_day'] ?? 5;
        
        if ($amount > 0) {
            // Update settings
            $stmt = $pdo->prepare("UPDATE settings SET maintenance_fee = ?, due_date_day = ?");
            $stmt->execute([$amount, $due_date_day]);
            
            // Generate maintenance fees for all occupied flats for current and future months
            $currentMonth = date('Y-m');
            
            // Get all occupied flats
            $flatsStmt = $pdo->query("SELECT id FROM flats WHERE status = 'occupied'");
            $flats = $flatsStmt->fetchAll();
            
            // Generate fees for current month and next 12 months
            for ($i = 0; $i <= 12; $i++) {
                $monthYear = date('Y-m', strtotime("+$i months", strtotime($currentMonth)));
                
                foreach ($flats as $flat) {
                    // Check if fee already exists for this flat/month
                    $checkStmt = $pdo->prepare("SELECT id FROM maintenance_fees WHERE flat_id = ? AND month_year = ?");
                    $checkStmt->execute([$flat['id'], $monthYear]);
                    $exists = $checkStmt->fetch();
                    
                    if (!$exists) {
                        // Calculate due date (day of month from settings)
                        $dueDate = date('Y-m-d', strtotime($monthYear . '-' . str_pad($due_date_day, 2, '0', STR_PAD_LEFT)));
                        
                        // Insert new maintenance fee
                        $insertStmt = $pdo->prepare("INSERT INTO maintenance_fees (flat_id, month_year, amount, due_date, status) 
                                                    VALUES (?, ?, ?, ?, 'pending')");
                        $insertStmt->execute([$flat['id'], $monthYear, $amount, $dueDate]);
                    }
                }
            }
            
            $_SESSION['success_message'] = 'Maintenance fee updated successfully and applied to all occupied flats!';
            header('Location: maintenance.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Please enter a valid amount';
        }
    }
    
    if (isset($_POST['add_special_charges'])) {
        // Add special charges to specific flat/month
        $flatId = $_POST['flat_id'] ?? 0;
        $monthYear = $_POST['month_year'] ?? '';
        $chargeType = $_POST['charge_type'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        
        if ($flatId && $monthYear && $chargeType && $amount > 0) {
            // Get existing maintenance fee
            $stmt = $pdo->prepare("SELECT * FROM maintenance_fees WHERE flat_id = ? AND month_year = ?");
            $stmt->execute([$flatId, $monthYear]);
            $fee = $stmt->fetch();
            
            if ($fee) {
                // Update based on charge type
                $updateFields = [];
                $params = [];
                
                if ($chargeType === 'penalty') {
                    $updateFields[] = 'penalty = ?';
                    $params[] = $amount;
                } elseif ($chargeType === 'late_fee') {
                    $updateFields[] = 'late_fee = ?';
                    $params[] = $amount;
                } elseif ($chargeType === 'special') {
                    $updateFields[] = 'special_charges = ?';
                    $params[] = $amount;
                }
                
                if (!empty($updateFields)) {
                    $params[] = $flatId;
                    $params[] = $monthYear;
                    
                    $stmt = $pdo->prepare("UPDATE maintenance_fees SET " . implode(', ', $updateFields) . " WHERE flat_id = ? AND month_year = ?");
                    $stmt->execute($params);
                    
                    $_SESSION['success_message'] = 'Special charge added successfully!';
                    header('Location: maintenance.php');
                    exit;
                }
            } else {
                $_SESSION['error_message'] = 'Maintenance fee record not found for this flat and month';
            }
        } else {
            $_SESSION['error_message'] = 'Please fill in all required fields';
        }
    }
}

// Get current settings
$settingsStmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsStmt->fetch();

// Get all flats with owner information
$flatsStmt = $pdo->query("SELECT f.*, u.username as owner_name FROM flats f LEFT JOIN users u ON f.id = u.flat_id AND u.role = 'owner' WHERE f.status = 'occupied' ORDER BY f.flat_number");
$flats = $flatsStmt->fetchAll();

// Get recent maintenance fees (last 3 months)
$recentStmt = $pdo->query("SELECT mf.*, f.flat_number, u.username as owner_name FROM maintenance_fees mf 
                          JOIN flats f ON mf.flat_id = f.id 
                          LEFT JOIN users u ON f.id = u.flat_id AND u.role = 'owner' 
                          ORDER BY mf.created_at DESC LIMIT 20");
$recentFees = $recentStmt->fetchAll();
?>
                <!-- Maintenance Management -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Maintenance Fee Management
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Set and manage maintenance fees for all flats
                    </p>
                </div>
                
                <?php if (!empty($_SESSION['success_message'])): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                        <?= $_SESSION['success_message'] ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                        <?= $_SESSION['error_message'] ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Set Global Maintenance Fee -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Set Global Maintenance Fee</h2>
                    <form method="POST" action="" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Monthly Maintenance Amount (₹)
                                </label>
                                <input type="number" name="amount" id="amount" 
                                    value="<?= htmlspecialchars($settings['maintenance_fee'] ?? 0) ?>" 
                                    min="0" step="0.01" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            
                            <div>
                                <label for="due_date_day" class="block text-sm font-medium text-gray-700 mb-2">
                                    Due Date (day of month)
                                </label>
                                <input type="number" name="due_date_day" id="due_date_day" 
                                    value="<?= htmlspecialchars($settings['due_date_day'] ?? 5) ?>" 
                                    min="1" max="31" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <p class="mt-1 text-xs text-gray-500">
                                    Maintenance fees will be due on this day of each month
                                </p>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" name="set_maintenance_fee"
                                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                                Update Maintenance Fee
                            </button>
                        </div>
                    </form>
                    
                    <p class="mt-3 text-sm text-gray-500">
                        <strong>Note:</strong> Updating the maintenance fee will automatically generate/update fee records for all occupied flats for the current and next 12 months.
                    </p>
                </div>
                
                <!-- Add Special Charges -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Add Special Charges</h2>
                    <form method="POST" action="" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="flat_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Flat
                                </label>
                                <select name="flat_id" id="flat_id"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">Select a flat</option>
                                    <?php foreach ($flats as $flat): ?>
                                        <option value="<?= $flat['id'] ?>">
                                            Flat #<?= htmlspecialchars($flat['flat_number']) ?> 
                                            (<?= htmlspecialchars($flat['owner_name'] ?? 'No Owner') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="month_year" class="block text-sm font-medium text-gray-700 mb-2">
                                    Month & Year
                                </label>
                                <input type="month" name="month_year" id="month_year"
                                    value="<?= date('Y-m') ?>"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="charge_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Charge Type
                                </label>
                                <select name="charge_type" id="charge_type"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="penalty">Penalty</option>
                                    <option value="late_fee">Late Fee</option>
                                    <option value="special">Special Charge (e.g., painting fund)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Amount (₹)
                                </label>
                                <input type="number" name="amount" id="amount" 
                                    min="0" step="0.01" required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" name="add_special_charges"
                                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                                Add Special Charge
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Recent Maintenance Fees -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Maintenance Fees</h2>
                    <?php if (!empty($recentFees)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Special Charges</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late Fee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penalty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Due</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($recentFees as $fee): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                Flat #<?= htmlspecialchars($fee['flat_number']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?= date('M Y', strtotime($fee['month_year'] . '-01')) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₹<?= number_format($fee['amount'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₹<?= number_format($fee['special_charges'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₹<?= number_format($fee['late_fee'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₹<?= number_format($fee['penalty'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₹<?= number_format($fee['amount'] + $fee['special_charges'] + $fee['late_fee'] + $fee['penalty'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₹<?= number_format($fee['paid_amount'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 py-0.5 
                                                    <?= $fee['status'] === 'paid' ? 'bg-green-100 text-green-800' :
                                                      ($fee['status'] === 'overdue' ? 'bg-red-100 text-red-800' :
                                                       ($fee['status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) ?>">
                                                    <?= ucfirst($fee['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No maintenance fees found.</p>
                    <?php endif; ?>
                </div>
<?php include __DIR__ . '/includes/footer.php'; ?>