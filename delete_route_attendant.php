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

// রেকর্ড ID সংগ্রহ করুন
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ রেকর্ড']);
    exit();
}

try {
    // প্রথমে রেকর্ডটি আছে কিনা চেক করুন
    $check_route_attendant = "SELECT * FROM route_attendant WHERE id = :id";
    $stmt = $pdo->prepare($check_route_attendant);
    $stmt->execute([':id' => $id]);
    $route_attendant = $stmt->fetch();
    
    if (!$route_attendant) {
        echo json_encode(['success' => false, 'message' => 'রেকর্ড পাওয়া যায়নি']);
        exit();
    }
    
    // রেকর্ড মুছে ফেলুন
    $delete_route_attendant = "DELETE FROM route_attendant WHERE id = :id";
    $stmt = $pdo->prepare($delete_route_attendant);
    $result = $stmt->execute([':id' => $id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'রেকর্ড সফলভাবে মুছে ফেলা হয়েছে']);
    } else {
        echo json_encode(['success' => false, 'message' => 'রেকর্ড মুছতে সমস্যা হয়েছে']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'সিস্টেমে সমস্যা হয়েছে: ' . $e->getMessage()]);
}
?>
