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

// রুট ID সংগ্রহ করুন
$route_id = (int)($_POST['route_id'] ?? 0);

if ($route_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ রুট ID']);
    exit();
}

try {
    // প্রথমে রুটটি আছে কিনা চেক করুন
    $check_route = "SELECT route_name FROM routes WHERE id = :id";
    $stmt = $pdo->prepare($check_route);
    $stmt->execute([':id' => $route_id]);
    $route = $stmt->fetch();
    
    if (!$route) {
        echo json_encode(['success' => false, 'message' => 'রুট পাওয়া যায়নি']);
        exit();
    }
    
    // ট্রানজ্যাকশন শুরু করুন
    $pdo->beginTransaction();
    
    // উপ-গন্তব্য মুছে ফেলুন (CASCADE দিয়ে অটো মুছে যাবে, কিন্তু স্পষ্ট করার জন্য)
    $delete_sub_destinations = "DELETE FROM route_sub_destinations WHERE route_id = :route_id";
    $stmt = $pdo->prepare($delete_sub_destinations);
    $stmt->execute([':route_id' => $route_id]);
    
    // রুট মুছে ফেলুন
    $delete_route = "DELETE FROM routes WHERE id = :id";
    $stmt = $pdo->prepare($delete_route);
    $result = $stmt->execute([':id' => $route_id]);
    
    if ($result) {
        // ট্রানজ্যাকশন কমিট করুন
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'রুট সফলভাবে মুছে ফেলা হয়েছে']);
    } else {
        // ট্রানজ্যাকশন রোলব্যাক করুন
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'রুট মুছতে সমস্যা হয়েছে']);
    }
    
} catch (PDOException $e) {
    // ট্রানজ্যাকশন রোলব্যাক করুন
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'সিস্টেমে সমস্যা হয়েছে: ' . $e->getMessage()]);
}
?>
