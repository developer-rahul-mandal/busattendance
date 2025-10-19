<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// POST রিকোয়েস্ট চেক করুন
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: route_list.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$route_id = (int)($_POST['route_id'] ?? 0);
$route_name = trim($_POST['route_name'] ?? '');
$route_code = trim($_POST['route_code'] ?? '');
$start_location = trim($_POST['start_location'] ?? '');
$end_location = trim($_POST['end_location'] ?? '');
$distance = (float)($_POST['distance'] ?? 0);
$estimated_time = (int)($_POST['estimated_time'] ?? 0);
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';

// উপ-গন্তব্য ডেটা সংগ্রহ করুন
$sub_destinations = $_POST['sub_destinations'] ?? [];
$sub_distances = $_POST['sub_distances'] ?? [];
$sub_times = $_POST['sub_times'] ?? [];

// ভ্যালিডেশন
$errors = [];

if ($route_id <= 0) {
    $errors[] = "অবৈধ রুট ID";
}

if (empty($route_name)) {
    $errors[] = "রুটের নাম প্রয়োজন";
}

if (empty($route_code)) {
    $errors[] = "রুট কোড প্রয়োজন";
}

if (empty($start_location)) {
    $errors[] = "শুরু গন্তব্য প্রয়োজন";
}

if (empty($end_location)) {
    $errors[] = "শেষ গন্তব্য প্রয়োজন";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: route_list.php');
    exit();
}

try {
    // রুট কোড ইউনিক চেক করুন (অন্য রুটের জন্য)
    $check_route = "SELECT COUNT(*) FROM routes WHERE route_code = :route_code AND id != :id";
    $stmt = $pdo->prepare($check_route);
    $stmt->execute([':route_code' => $route_code, ':id' => $route_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই রুট কোড ইতিমধ্যে অন্য রুটে ব্যবহার করা হয়েছে";
        header('Location: edit_route.php?id=' . $route_id);
        exit();
    }
    
    // ট্রানজ্যাকশন শুরু করুন
    $pdo->beginTransaction();
    
    // রুট আপডেট করুন
    $update_route = "
    UPDATE routes 
    SET route_name = :route_name, 
        route_code = :route_code, 
        start_location = :start_location, 
        end_location = :end_location, 
        distance = :distance, 
        estimated_time = :estimated_time, 
        description = :description, 
        status = :status,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
    ";
    
    $stmt = $pdo->prepare($update_route);
    $result = $stmt->execute([
        ':route_name' => $route_name,
        ':route_code' => $route_code,
        ':start_location' => $start_location,
        ':end_location' => $end_location,
        ':distance' => $distance,
        ':estimated_time' => $estimated_time,
        ':description' => $description,
        ':status' => $status,
        ':id' => $route_id
    ]);
    
    if (!$result) {
        throw new Exception("রুট আপডেট করতে সমস্যা হয়েছে");
    }
    
    // বিদ্যমান উপ-গন্তব্য মুছে ফেলুন
    $delete_sub_destinations = "DELETE FROM route_sub_destinations WHERE route_id = :route_id";
    $stmt = $pdo->prepare($delete_sub_destinations);
    $stmt->execute([':route_id' => $route_id]);
    
    // নতুন উপ-গন্তব্য যোগ করুন
    if (!empty($sub_destinations)) {
        $insert_sub_destination = "
        INSERT INTO route_sub_destinations (route_id, destination_name, distance, estimated_time, sequence_order) 
        VALUES (:route_id, :destination_name, :distance, :estimated_time, :sequence_order)
        ";
        
        $stmt = $pdo->prepare($insert_sub_destination);
        
        foreach ($sub_destinations as $index => $destination) {
            if (!empty(trim($destination))) {
                $stmt->execute([
                    ':route_id' => $route_id,
                    ':destination_name' => trim($destination),
                    ':distance' => (float)($sub_distances[$index] ?? 0),
                    ':estimated_time' => (int)($sub_times[$index] ?? 0),
                    ':sequence_order' => $index + 1
                ]);
            }
        }
    }
    
    // ট্রানজ্যাকশন কমিট করুন
    $pdo->commit();
    
    $_SESSION['success_message'] = "রুট সফলভাবে আপডেট করা হয়েছে!";
    header('Location: route_list.php');
    exit();
    
} catch (Exception $e) {
    // ট্রানজ্যাকশন রোলব্যাক করুন
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: edit_route.php?id=' . $route_id);
    exit();
}
?>
