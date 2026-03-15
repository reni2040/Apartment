<?php
$config = include __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

// Get user's flat information
$userId = $_SESSION['user_id'];
$flatInfo = null;

// Get user's flat
$stmt = $pdo->prepare("SELECT f.* FROM flats f JOIN users u ON f.id = u.flat_id WHERE u.id = ?");
$stmt->execute([$userId]);
$flatInfo = $stmt->fetch();

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $reportType = $_POST['report_type'] ?? 'monthly';
    $monthYear = $_POST['month_year'] ?? date('Y-m');
    
    // Redirect to report generation script
    header("Location: generate_report.php?type=$reportType&month_year=$monthYear");
    exit;
}
?>
                <!-- Reports Section -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        Maintenance Fee Reports
                        <?php if ($flatInfo): ?>
                            <span class="ml-3 px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs rounded">
                                Flat #<?= htmlspecialchars($flatInfo['flat_number']) ?>
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Download your maintenance fee payment reports
                    </p>
                </div>
                
                <?php if (!$flatInfo): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                        No flat assigned to your account. Please contact the administrator.
                    </div>
                <?php else: ?>
                    <!-- Report Generation Form -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Generate Report</h2>
                        <form method="POST" action="" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Report Type
                                    </label>
                                    <select name="report_type" id="report_type"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="monthly">Monthly Report</option>
                                        <option value="annual">Annual Report</option>
                                        <option value="custom">Custom Date Range</option>
                                    </select>
                                </div>
                                
                                <div id="month_year_field">
                                    <label for="month_year" class="block text-sm font-medium text-gray-700 mb-2">
                                        Month & Year
                                    </label>
                                    <input type="month" name="month_year" id="month_year"
                                        value="<?= date('Y-m') ?>"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                                
                                <div id="custom_date_field" class="hidden">
                                    <div class="space-y-2">
                                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                            Start Date
                                        </label>
                                        <input type="date" name="start_date" id="start_date"
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                            End Date
                                        </label>
                                        <input type="date" name="end_date" id="end_date"
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <button type="submit" name="generate_report"
                                    class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                                    Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Report History -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Report History</h2>
                        <p class="text-sm text-gray-500 mb-4">
                            Your generated reports will appear here for download.
                        </p>
                        
                        <!-- In a real system, you would store generated reports in a table -->
                        <!-- For now, we'll show a placeholder -->
                        <div class="text-center py-8">
                            <p class="text-gray-500">No reports generated yet.</p>
                            <p class="mt-2 text-sm text-gray-600">
                                Generate a report above to see it here.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>