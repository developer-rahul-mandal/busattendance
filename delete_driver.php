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

// ড্রাইভার ID সংগ্রহ করুন
$driver_id = (int)($_POST['driver_id'] ?? 0);

if ($driver_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ ড্রাইভার ID']);
    exit();
}

try {
    // প্রথমে ড্রাইভারটি আছে কিনা চেক করুন
    $check_driver = "SELECT driver_name FROM drivers WHERE id = :id";
    $stmt = $pdo->prepare($check_driver);
    $stmt->execute([':id' => $driver_id]);
    $driver = $stmt->fetch();
    
    if (!$driver) {
        echo json_encode(['success' => false, 'message' => 'ড্রাইভার পাওয়া যায়নি']);
        exit();
    }
    
    // ড্রাইভার মুছে ফেলুন
    $delete_driver = "DELETE FROM drivers WHERE id = :id";
    $stmt = $pdo->prepare($delete_driver);
    $result = $stmt->execute([':id' => $driver_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'ড্রাইভার সফলভাবে মুছে ফেলা হয়েছে']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ড্রাইভার মুছতে সমস্যা হয়েছে']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'সিস্টেমে সমস্যা হয়েছে: ' . $e->getMessage()]);
}
?>
