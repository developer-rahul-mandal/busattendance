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
$bus_id = (int)($_POST['bus_id'] ?? 0);

if ($bus_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ বাস ID']);
    exit();
}

try {
    // প্রথমে বাসটি আছে কিনা চেক করুন
    $check_bus = "SELECT bus_number FROM buses WHERE id = :id";
    $stmt = $pdo->prepare($check_bus);
    $stmt->execute([':id' => $bus_id]);
    $bus = $stmt->fetch();
    
    if (!$bus) {
        echo json_encode(['success' => false, 'message' => 'বাস পাওয়া যায়নি']);
        exit();
    }
    
    // বাস মুছে ফেলুন
    $delete_bus = "DELETE FROM buses WHERE id = :id";
    $stmt = $pdo->prepare($delete_bus);
    $result = $stmt->execute([':id' => $bus_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'বাস সফলভাবে মুছে ফেলা হয়েছে']);
    } else {
        echo json_encode(['success' => false, 'message' => 'বাস মুছতে সমস্যা হয়েছে']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'সিস্টেমে সমস্যা হয়েছে: ' . $e->getMessage()]);
}
?>
