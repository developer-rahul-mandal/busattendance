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
    header('Location: bus_list.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$bus_id = (int)($_POST['bus_id'] ?? 0);
$bus_number = trim($_POST['bus_number'] ?? '');
$bus_name = trim($_POST['bus_name'] ?? '');
$capacity = (int)($_POST['capacity'] ?? 0);
$bus_type = trim($_POST['bus_type'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';

// ভ্যালিডেশন
$errors = [];

if ($bus_id <= 0) {
    $errors[] = "অবৈধ বাস ID";
}

if (empty($bus_number)) {
    $errors[] = "বাস নম্বর প্রয়োজন";
}

if ($capacity <= 0) {
    $errors[] = "ধারণক্ষমতা ১ এর বেশি হতে হবে";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: bus_list.php');
    exit();
}

try {
    // বাস নম্বর ইউনিক চেক করুন (অন্য বাসের জন্য)
    $check_bus = "SELECT COUNT(*) FROM buses WHERE bus_number = :bus_number AND id != :id";
    $stmt = $pdo->prepare($check_bus);
    $stmt->execute([':bus_number' => $bus_number, ':id' => $bus_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই বাস নম্বর ইতিমধ্যে অন্য বাসে ব্যবহার করা হয়েছে";
        header('Location: edit_bus.php?id=' . $bus_id);
        exit();
    }
    
    // বাস আপডেট করুন
    $update_bus = "
    UPDATE buses 
    SET bus_number = :bus_number, 
        bus_name = :bus_name, 
        capacity = :capacity, 
        bus_type = :bus_type, 
        description = :description, 
        status = :status,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
    ";
    
    $stmt = $pdo->prepare($update_bus);
    $result = $stmt->execute([
        ':bus_number' => $bus_number,
        ':bus_name' => $bus_name,
        ':capacity' => $capacity,
        ':bus_type' => $bus_type,
        ':description' => $description,
        ':status' => $status,
        ':id' => $bus_id
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "বাস সফলভাবে আপডেট করা হয়েছে!";
        header('Location: bus_list.php');
        exit();
    } else {
        $_SESSION['error_message'] = "বাস আপডেট করতে সমস্যা হয়েছে";
        header('Location: edit_bus.php?id=' . $bus_id);
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: edit_bus.php?id=' . $bus_id);
    exit();
}
?>
