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
    header('Location: add_bus.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$bus_number = trim($_POST['bus_number'] ?? '');
$bus_name = trim($_POST['bus_name'] ?? '');
$capacity = (int)($_POST['capacity'] ?? 0);
$bus_type = trim($_POST['bus_type'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';

// ভ্যালিডেশন
$errors = [];

if (empty($bus_number)) {
    $errors[] = "বাস নম্বর প্রয়োজন";
}

if ($capacity <= 0) {
    $errors[] = "ধারণক্ষমতা ১ এর বেশি হতে হবে";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: add_bus.php');
    exit();
}

try {
    // প্রথমে বাস টেবিল তৈরি করুন (যদি না থাকে)
    $create_bus_table = "
    CREATE TABLE IF NOT EXISTS buses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bus_number VARCHAR(50) UNIQUE NOT NULL,
        bus_name VARCHAR(100),
        capacity INT NOT NULL,
        bus_type VARCHAR(50),
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_bus_table);
    
    // বাস নম্বর ইউনিক চেক করুন
    $check_bus = "SELECT COUNT(*) FROM buses WHERE bus_number = :bus_number";
    $stmt = $pdo->prepare($check_bus);
    $stmt->execute([':bus_number' => $bus_number]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই বাস নম্বর ইতিমধ্যে বিদ্যমান";
        header('Location: add_bus.php');
        exit();
    }
    
    // বাস যোগ করুন
    $insert_bus = "
    INSERT INTO buses (bus_number, bus_name, capacity, bus_type, description, status) 
    VALUES (:bus_number, :bus_name, :capacity, :bus_type, :description, :status)
    ";
    
    $stmt = $pdo->prepare($insert_bus);
    $result = $stmt->execute([
        ':bus_number' => $bus_number,
        ':bus_name' => $bus_name,
        ':capacity' => $capacity,
        ':bus_type' => $bus_type,
        ':description' => $description,
        ':status' => $status
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "বাস সফলভাবে যোগ করা হয়েছে!";
        header('Location: add_bus.php');
        exit();
    } else {
        $_SESSION['error_message'] = "বাস যোগ করতে সমস্যা হয়েছে";
        header('Location: add_bus.php');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: add_bus.php');
    exit();
}
?>
