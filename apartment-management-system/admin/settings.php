<?php
$config = include __DIR__ . '/../config.php';
include __DIR__ . '/includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_society_info'])) {
        // Update society information
        $societyName = $_POST['society_name'] ?? '';
        $address = $_POST['address'] ?? '';
        $gstNumber = $_POST['gst_number'] ?? '';
        $invoicePrefix = $_POST['invoice_prefix'] ?? 'RCPT';
        
        $stmt = $pdo->prepare("UPDATE settings SET society_name = ?, address = ?, gst_number = ?, invoice_prefix = ?");
        $stmt->execute([$societyName, $address, $gstNumber, $invoicePrefix]);
        
        $_SESSION['success_message'] = 'Society information updated successfully!';
        header('Location: settings.php');
        exit;
    }
    
    if (isset($_POST['update_email_settings'])) {
        // Update email settings
        $smtpHost = $_POST['smtp_host'] ?? '';
        $smtpPort = $_POST['smtp_port'] ?? 587;
        $smtpUser = $_POST['smtp_user'] ?? '';
        $smtpPass = $_POST['smtp_pass'] ?? '';
        $smtpSecure = $_POST['smtp_secure'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE settings SET email_smtp_host = ?, email_smtp_port = ?, email_smtp_user = ?, email_smtp_pass = ?, email_smtp_secure = ?");
        $stmt->execute([$smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpSecure]);
        
        $_SESSION['success_message'] = 'Email settings updated successfully!';
        header('Location: settings.php');
        exit;
    }
    
    if (isset($_POST['update_sms_settings'])) {
        // Update SMS settings
        $apiKey = $_POST['sms_api_key'] ?? '';
        $apiSecret = $_POST['sms_api_secret'] ?? '';
        $senderId = $_POST['sms_sender_id'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE settings SET sms_api_key = ?, sms_api_secret = ?, sms_sender_id = ?");
        $stmt->execute([$apiKey, $apiSecret, $senderId]);
        
        $_SESSION['success_message'] = 'SMS settings updated successfully!';
        header('Location: settings.php');
        exit;
    }
}

// Get current settings
$settingsStmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsStmt->fetch();
?>
                <!-- Settings Management -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Society Settings
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Configure society information and system settings
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
                
                <!-- Society Information -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Society Information</h2>
                    <form method="POST" action="" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="society_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Society Name
                                </label>
                                <input type="text" name="society_name" id="society_name" 
                                    value="<?= htmlspecialchars($settings['society_name'] ?? '') ?>" 
                                    required
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Address
                                </label>
                                <textarea name="address" id="address" rows="3"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="gst_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    GST Number (if applicable)
                                </label>
                                <input type="text" name="gst_number" id="gst_number" 
                                    value="<?= htmlspecialchars($settings['gst_number'] ?? '') ?>" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            
                            <div>
                                <label for="invoice_prefix" class="block text-sm font-medium text-gray-700 mb-2">
                                    Invoice Prefix
                                </label>
                                <input type="text" name="invoice_prefix" id="invoice_prefix" 
                                    value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'RCPT') ?>" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" name="update_society_info"
                                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                                Update Society Information
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Email Settings -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Email Settings</h2>
                    <form method="POST" action="" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Host
                                </label>
                                <input type="text" name="smtp_host" id="smtp_host" 
                                    value="<?= htmlspecialchars($settings['email_smtp_host'] ?? '') ?>" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            
                            <div>
                                <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Port
                                </label>
                                <input type="number" name="smtp_port" id="smtp_port" 
                                    value="<?= htmlspecialchars($settings['email_smtp_port'] ?? 587) ?>" 
                                    min="1" max="65535"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_user" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Username
                                </label>
                                <input type="text" name="smtp_user" id="smtp_user" 
                                    value="<?= htmlspecialchars($settings['email_smtp_user'] ?? '') ?>" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            
                            <div>
                                <label for="smtp_pass" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Password
                                </label>
                                <input type="password" name="smtp_pass" id="smtp_pass" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:gap-4">
                            <div>
                                <label for="smtp_secure" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMTP Secure Connection
                                </label>
                                <select name="smtp_secure" id="smtp_secure"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="" <?= ($settings['email_smtp_secure'] ?? '') === '' ? 'selected' : '' ?>>None</option>
                                    <option value="tls" <?= ($settings['email_smtp_secure'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($settings['email_smtp_secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" name="update_email_settings"
                                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                                Update Email Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- SMS Settings -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">SMS Settings</h2>
                    <form method="POST" action="" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="sms_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMS API Key
                                </label>
                                <input type="text" name="sms_api_key" id="sms_api_key" 
                                    value="<?= htmlspecialchars($settings['sms_api_key'] ?? '') ?>" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            
                            <div>
                                <label for="sms_api_secret" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMS API Secret
                                </label>
                                <input type="text" name="sms_api_secret" id="sms_api_secret" 
                                    value="<?= htmlspecialchars($settings['sms_api_secret'] ?? '') ?>" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="sms_sender_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    SMS Sender ID
                                </label>
                                <input type="text" name="sms_sender_id" id="sms_sender_id" 
                                    value="<?= htmlspecialchars($settings['sms_sender_id'] ?? '') ?>" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" name="update_sms_settings"
                                class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:offset-2 focus-visible:outline-indigo-600">
                                Update SMS Settings
                            </button>
                        </div>
                    </form>
                </div>
<?php include __DIR__ . '/includes/footer.php'; ?>