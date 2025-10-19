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
    header('Location: add_driver.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
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
    header('Location: add_driver.php');
    exit();
}

try {
    // প্রথমে ড্রাইভার টেবিল তৈরি করুন (যদি না থাকে)
    $create_driver_table = "
    CREATE TABLE IF NOT EXISTS drivers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_name VARCHAR(100) NOT NULL,
        driver_id_number VARCHAR(50) UNIQUE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100),
        license_number VARCHAR(50) NOT NULL,
        license_expiry DATE,
        experience_years INT DEFAULT 0,
        salary DECIMAL(10,2) DEFAULT 0,
        address TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        gender ENUM('male', 'female') DEFAULT 'male',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_driver_table);
    
    // ড্রাইভার আইডি ইউনিক চেক করুন
    $check_driver = "SELECT COUNT(*) FROM drivers WHERE driver_id_number = :driver_id_number";
    $stmt = $pdo->prepare($check_driver);
    $stmt->execute([':driver_id_number' => $driver_id_number]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই ড্রাইভার আইডি ইতিমধ্যে বিদ্যমান";
        header('Location: add_driver.php');
        exit();
    }
    
    // লাইসেন্স নম্বর ইউনিক চেক করুন
    $check_license = "SELECT COUNT(*) FROM drivers WHERE license_number = :license_number";
    $stmt = $pdo->prepare($check_license);
    $stmt->execute([':license_number' => $license_number]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই লাইসেন্স নম্বর ইতিমধ্যে বিদ্যমান";
        header('Location: add_driver.php');
        exit();
    }
    
    // ড্রাইভার যোগ করুন
    $insert_driver = "
    INSERT INTO drivers (driver_name, driver_id_number, phone, email, license_number, license_expiry, experience_years, salary, address, status, gender) 
    VALUES (:driver_name, :driver_id_number, :phone, :email, :license_number, :license_expiry, :experience_years, :salary, :address, :status, :gender)
    ";
    
    $stmt = $pdo->prepare($insert_driver);
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
        ':gender' => $gender
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "ড্রাইভার সফলভাবে যোগ করা হয়েছে!";
        header('Location: add_driver.php');
        exit();
    } else {
        $_SESSION['error_message'] = "ড্রাইভার যোগ করতে সমস্যা হয়েছে";
        header('Location: add_driver.php');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: add_driver.php');
    exit();
}
?>
