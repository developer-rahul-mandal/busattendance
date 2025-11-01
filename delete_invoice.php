<?php
session_start();
require_once 'config/database.php';

// JSON রেসপন্সের জন্য হেডার সেট করুন
header('Content-Type: application/json');

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'অনুমতি নেই']);
    exit();
}

// POST রিকোয়েস্ট চেক করুন
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'অবৈধ রিকোয়েস্ট']);
    exit();
}

// বাস ID সংগ্রহ করুন
$invoice_id = (int)($_POST['id'] ?? 0);

if ($invoice_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ চালান']);
    exit();
}

try {
    // প্রথমে বাসটি আছে কিনা চেক করুন
    $check_student = "SELECT * FROM invoices WHERE id = :id";
    $stmt = $pdo->prepare($check_student);
    $stmt->execute([':id' => $invoice_id]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => 'চালানটি পাওয়া যায়নি']);
        exit();
    }
    
    // বাস মুছে ফেলুন
    $delete_invoice = "DELETE FROM invoices WHERE id = :id";
    $stmt = $pdo->prepare($delete_invoice);
    $result = $stmt->execute([':id' => $invoice_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'চালানটি সফলভাবে মুছে ফেলা হয়েছে']);
    } else {
        echo json_encode(['success' => false, 'message' => 'চালানটি মুছতে সমস্যা হয়েছে']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'সিস্টেমে সমস্যা হয়েছে: ' . $e->getMessage()]);
}
?>
