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
    header('Location: add_route.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
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
    header('Location: add_route.php');
    exit();
}

try {
    // প্রথমে রুট টেবিল তৈরি করুন (যদি না থাকে)
    $create_route_table = "
    CREATE TABLE IF NOT EXISTS routes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        route_name VARCHAR(100) NOT NULL,
        route_code VARCHAR(50) UNIQUE NOT NULL,
        start_location VARCHAR(100) NOT NULL,
        end_location VARCHAR(100) NOT NULL,
        distance DECIMAL(8,2) DEFAULT 0,
        estimated_time INT DEFAULT 0,
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_route_table);
    
    // উপ-গন্তব্য টেবিল তৈরি করুন
    $create_sub_destination_table = "
    CREATE TABLE IF NOT EXISTS route_sub_destinations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        route_id INT NOT NULL,
        destination_name VARCHAR(100) NOT NULL,
        distance DECIMAL(8,2) DEFAULT 0,
        estimated_time INT DEFAULT 0,
        sequence_order INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($create_sub_destination_table);
    
    // রুট কোড ইউনিক চেক করুন
    $check_route = "SELECT COUNT(*) FROM routes WHERE route_code = :route_code";
    $stmt = $pdo->prepare($check_route);
    $stmt->execute([':route_code' => $route_code]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই রুট কোড ইতিমধ্যে বিদ্যমান";
        header('Location: add_route.php');
        exit();
    }
    
    // ট্রানজ্যাকশন শুরু করুন
    $pdo->beginTransaction();
    
    // রুট যোগ করুন
    $insert_route = "
    INSERT INTO routes (route_name, route_code, start_location, end_location, distance, estimated_time, description, status) 
    VALUES (:route_name, :route_code, :start_location, :end_location, :distance, :estimated_time, :description, :status)
    ";
    
    $stmt = $pdo->prepare($insert_route);
    $result = $stmt->execute([
        ':route_name' => $route_name,
        ':route_code' => $route_code,
        ':start_location' => $start_location,
        ':end_location' => $end_location,
        ':distance' => $distance,
        ':estimated_time' => $estimated_time,
        ':description' => $description,
        ':status' => $status
    ]);
    
    if (!$result) {
        throw new Exception("রুট যোগ করতে সমস্যা হয়েছে");
    }
    
    $route_id = $pdo->lastInsertId();
    
    // উপ-গন্তব্য যোগ করুন
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
    
    $_SESSION['success_message'] = "রুট সফলভাবে যোগ করা হয়েছে!";
    header('Location: add_route.php');
    exit();
    
} catch (Exception $e) {
    // ট্রানজ্যাকশন রোলব্যাক করুন
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: add_route.php');
    exit();
}
?>
