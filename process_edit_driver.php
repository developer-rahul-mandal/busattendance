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
    header('Location: driver_list.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$driver_id = (int)($_POST['driver_id'] ?? 0);
$driver_name = trim($_POST['driver_name'] ?? '');
$driver_id_number = trim($_POST['driver_id_number'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$license_number = trim($_POST['license_number'] ?? '');
$license_expiry = $_POST['license_expiry'] ?? '';
$experience_years = (int)($_POST['experience_years'] ?? 0);
$salary = (int)($_POST['salary'] ?? 0);
$address = trim($_POST['address'] ?? '');
$status = $_POST['status'] ?? 'active';
$gender = $_POST['gender'] ?? 'male';

// ভ্যালিডেশন
$errors = [];

if ($driver_id <= 0) {
    $errors[] = "অবৈধ ড্রাইভার ID";
}

if (empty($driver_name)) {
    $errors[] = "ড্রাইভারের নাম প্রয়োজন";
}

if (empty($driver_id_number)) {
    $errors[] = "ড্রাইভার আইডি নম্বর প্রয়োজন";
}

if (empty($phone)) {
    $errors[] = "ফোন নম্বর প্রয়োজন";
}

if (empty($license_number)) {
    $errors[] = "লাইসেন্স নম্বর প্রয়োজন";
}

// ইমেইল ভ্যালিডেশন (যদি দেওয়া হয়)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "সঠিক ইমেইল ঠিকানা দিন";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: driver_list.php');
    exit();
}

try {
    // ড্রাইভার আইডি ইউনিক চেক করুন (অন্য ড্রাইভারের জন্য)
    $check_driver = "SELECT COUNT(*) FROM drivers WHERE driver_id_number = :driver_id_number AND id != :id";
    $stmt = $pdo->prepare($check_driver);
    $stmt->execute([':driver_id_number' => $driver_id_number, ':id' => $driver_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই ড্রাইভার আইডি ইতিমধ্যে অন্য ড্রাইভারে ব্যবহার করা হয়েছে";
        header('Location: edit_driver.php?id=' . $driver_id);
        exit();
    }
    
    // লাইসেন্স নম্বর ইউনিক চেক করুন (অন্য ড্রাইভারের জন্য)
    $check_license = "SELECT COUNT(*) FROM drivers WHERE license_number = :license_number AND id != :id";
    $stmt = $pdo->prepare($check_license);
    $stmt->execute([':license_number' => $license_number, ':id' => $driver_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই লাইসেন্স নম্বর ইতিমধ্যে অন্য ড্রাইভারে ব্যবহার করা হয়েছে";
        header('Location: edit_driver.php?id=' . $driver_id);
        exit();
    }
    
    // ড্রাইভার আপডেট করুন
    $update_driver = "
    UPDATE drivers 
    SET driver_name = :driver_name, 
        driver_id_number = :driver_id_number, 
        phone = :phone, 
        email = :email, 
        license_number = :license_number, 
        license_expiry = :license_expiry, 
        experience_years = :experience_years, 
        salary = :salary, 
        address = :address, 
        status = :status, 
        gender = :gender,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
    ";
    
    $stmt = $pdo->prepare($update_driver);
    $result = $stmt->execute([
        ':driver_name' => $driver_name,
        ':driver_id_number' => $driver_id_number,
        ':phone' => $phone,
        ':email' => $email,
        ':license_number' => $license_number,
        ':license_expiry' => !empty($license_expiry) ? $license_expiry : null,
        ':experience_years' => $experience_years,
        ':salary' => $salary,
        ':address' => $address,
        ':status' => $status,
        ':gender' => $gender,
        ':id' => $driver_id
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "ড্রাইভার সফলভাবে আপডেট করা হয়েছে!";
        header('Location: driver_list.php');
        exit();
    } else {
        $_SESSION['error_message'] = "ড্রাইভার আপডেট করতে সমস্যা হয়েছে";
        header('Location: edit_driver.php?id=' . $driver_id);
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: edit_driver.php?id=' . $driver_id);
    exit();
}
?>
