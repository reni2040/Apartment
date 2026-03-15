<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../../login.php');
    exit;
}

$config = include __DIR__ . '/../config.php';

// Get parameters
$reportType = $_GET['type'] ?? 'monthly';
$monthYear = $_GET['month_year'] ?? date('Y-m');

// Get user's flat information
$userId = $_SESSION['user_id'];
$flatInfo = null;

// Get user's flat
$stmt = $pdo->prepare("SELECT f.* FROM flats f JOIN users u ON f.id = u.flat_id WHERE u.id = ?");
$stmt->execute([$userId]);
$flatInfo = $stmt->fetch();

if (!$flatInfo) {
    die('No flat assigned to your account');
}

// For simplicity, we'll generate a PDF report on the fly
// In a real system, you might use a library like TCPDF or Dompdf
// For this example, we'll generate a simple text-based report that can be saved as PDF

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="maintenance_report_' . $flatInfo['flat_number'] . '_' . $reportType . '.pdf"');

// Generate PDF content (simplified)
$content = "%PDF-1.4\n";
$content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
$content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
$content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
$content .= "4 0 obj\n<< /Length 5 0 R >>\nstream\n";

$content .= "/F1 12 Tf\n72 720 Td\n";

// Add society name
$content .= "(" . htmlspecialchars($config['society']['name'] ?? 'Apartment Management System') . ") Tj\n0 -14 Td\n";

// Add report title
$content .= "(" . ucfirst($reportType) . " Maintenance Report) Tj\n0 -14 Td\n";

// Add flat info
$content .= "(Flat Number: " . htmlspecialchars($flatInfo['flat_number']) . ") Tj\n0 -14 Td\n";
$content .= "(Owner Name: " . htmlspecialchars($flatInfo['owner_name'] ?? 'N/A') . ") Tj\n0 -14 Td\n";

// Add date
$content .= "(Generated on: " . date('M d, Y') . ") Tj\n0 -20 Td\n";

// Add table header
$content .= "(Month) 90 Tl (Amount Due) 180 Tl (Amount Paid) 270 Tl (Status) Tj\n0 -14 Td\n";

// Add data
$stmt = $pdo->prepare("SELECT * FROM maintenance_fees WHERE flat_id = ? ORDER BY month_year DESC");
$stmt->execute([$flatInfo['id']]);
$records = $stmt->fetchAll();

foreach ($records as $record) {
    $content .= "(" . date('M Y', strtotime($record['month_year'] . '-01')) . ") 90 Tl ";
    $content .= "(₹" . number_format($record['amount'], 2) . ") 180 Tl ";
    $content .= "(₹" . number_format($record['paid_amount'], 2) . ") 270 Tl ";
    $content .= "(" . ucfirst($record['status']) . ") Tj\n0 -14 Td\n";
}

$content .= "endstream\nendobj\n";
$content .= "5 0 obj\n" . strlen($content) - strpos($content, "stream\n") - 7 . "\nendobj\n";
$content .= "xref\n0 6\n0000000000 65535 f \n";
$content .= "0000000010 00000 n \n";
$content .= "0000000060 00000 n \n";
$content .= "0000000117 00000 n \n";
$content .= "0000000235 00000 n \n";
$content .= "0000000385 00000 n \n";
$content .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . (strlen($content) - strpos($content, "startxref") - 9) . "\n%%EOF\n";

echo $content;
exit;
?>