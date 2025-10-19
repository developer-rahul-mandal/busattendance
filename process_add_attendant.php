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
    header('Location: add_attendant.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$attendant_name = trim($_POST['attendant_name'] ?? '');
$attendant_id_number = trim($_POST['attendant_id_number'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$experience_years = (int)($_POST['experience_years'] ?? 0);
$salary = (int)($_POST['salary'] ?? 0);
$address = trim($_POST['address'] ?? '');
$status = $_POST['status'] ?? 'active';

// ভ্যালিডেশন
$errors = [];

if (empty($attendant_name)) {
    $errors[] = "মহিলা পরিচারিকা নাম প্রয়োজন";
}

if (empty($attendant_id_number)) {
    $errors[] = "মহিলা পরিচারিকা আইডি নম্বর প্রয়োজন";
}

if (empty($phone)) {
    $errors[] = "ফোন নম্বর প্রয়োজন";
}

// ইমেইল ভ্যালিডেশন (যদি দেওয়া হয়)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "সঠিক ইমেইল ঠিকানা দিন";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: add_attendant.php');
    exit();
}

try {
    // প্রথমে মহিলা পরিচারিকা টেবিল তৈরি করুন (যদি না থাকে)
    $create_attendant_table = "
    CREATE TABLE IF NOT EXISTS attendants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attendant_name VARCHAR(100) NOT NULL,
        attendant_id_number VARCHAR(50) UNIQUE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100),
        experience_years INT DEFAULT 0,
        salary DECIMAL(10,2) DEFAULT 0,
        address TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_attendant_table);
    
    // মহিলা পরিচারিকা আইডি ইউনিক চেক করুন
    $check_attendant = "SELECT COUNT(*) FROM attendants WHERE attendant_id_number = :attendant_id_number";
    $stmt = $pdo->prepare($check_attendant);
    $stmt->execute([':attendant_id_number' => $attendant_id_number]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই মহিলা পরিচারিকা আইডি ইতিমধ্যে বিদ্যমান";
        header('Location: add_attendant.php');
        exit();
    }
    
    // মহিলা পরিচারিকা যোগ করুন
    $insert_attendant = "
    INSERT INTO attendants (attendant_name, attendant_id_number, phone, email, experience_years, salary, address, status) 
    VALUES (:attendant_name, :attendant_id_number, :phone, :email, :experience_years, :salary, :address, :status)
    ";
    
    $stmt = $pdo->prepare($insert_attendant);
    $result = $stmt->execute([
        ':attendant_name' => $attendant_name,
        ':attendant_id_number' => $attendant_id_number,
        ':phone' => $phone,
        ':email' => $email,
        ':experience_years' => $experience_years,
        ':salary' => $salary,
        ':address' => $address,
        ':status' => $status,
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "মহিলা পরিচারিকা সফলভাবে যোগ করা হয়েছে!";
        header('Location: add_attendant.php');
        exit();
    } else {
        $_SESSION['error_message'] = "মহিলা পরিচারিকা যোগ করতে সমস্যা হয়েছে";
        header('Location: add_attendant.php');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: add_attendant.php');
    exit();
}
?>
