<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../../login.php');
    exit;
}

$config = include __DIR__ . '/../config.php';

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenanceFeeId = $_POST['maintenance_fee_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    
    // Validate input
    if (!$maintenanceFeeId || !$amount || $amount <= 0) {
        $_SESSION['payment_error'] = 'Invalid payment details';
        header('Location: maintenance.php');
        exit;
    }
    
    try {
        // Get maintenance fee details
        $stmt = $pdo->prepare("SELECT mf.*, f.id as flat_id, u.id as user_id FROM maintenance_fees mf 
                              JOIN flats f ON mf.flat_id = f.id 
                              JOIN users u ON f.id = u.flat_id 
                              WHERE mf.id = ?");
        $stmt->execute([$maintenanceFeeId]);
        $maintenanceFee = $stmt->fetch();
        
        if (!$maintenanceFee) {
            $_SESSION['payment_error'] = 'Maintenance fee not found';
            header('Location: maintenance.php');
            exit;
        }
        
        // Check if user owns this flat
        if ($maintenanceFee['user_id'] != $_SESSION['user_id']) {
            $_SESSION['payment_error'] = 'Unauthorized access';
            header('Location: maintenance.php');
            exit;
        }
        
        // Check if amount is valid
        $balanceDue = $maintenanceFee['amount'] - $maintenanceFee['paid_amount'];
        if ($amount > $balanceDue) {
            $_SESSION['payment_error'] = 'Payment amount exceeds balance due';
            header('Location: maintenance.php');
            exit;
        }
        
        // Process payment (simulate successful payment)
        // In a real system, you would integrate with Razorpay/Paytm here
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Update maintenance fee paid amount
        $newPaidAmount = $maintenanceFee['paid_amount'] + $amount;
        $newStatus = 'pending';
        if ($newPaidAmount >= $maintenanceFee['amount']) {
            $newStatus = 'paid';
            $newPaidAmount = $maintenanceFee['amount']; // Ensure exact amount
        } elseif ($newPaidAmount > 0) {
            $newStatus = 'partial';
        }
        
        $stmt = $pdo->prepare("UPDATE maintenance_fees SET paid_amount = ?, status = ? WHERE id = ?");
        $stmt->execute([$newPaidAmount, $newStatus, $maintenanceFeeId]);
        
        // Record payment
        $stmt = $pdo->prepare("INSERT INTO payments (maintenance_fee_id, user_id, amount, payment_method, transaction_id, status, payment_date) 
                              VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $transactionId = 'PAY' . uniqid();
        $stmt->execute([$maintenanceFeeId, $_SESSION['user_id'], $amount, 'Online', $transactionId, 'success']);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['payment_success'] = 'Payment of ₹' . number_format($amount, 2) . ' processed successfully!';
        header('Location: maintenance.php?payment=success');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Payment error: " . $e->getMessage());
        $_SESSION['payment_error'] = 'Payment processing failed. Please try again.';
        header('Location: maintenance.php');
        exit;
    }
} else {
    header('Location: maintenance.php');
    exit;
}
?>