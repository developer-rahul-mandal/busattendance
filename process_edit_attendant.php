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
    header('Location: attendant_list.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$attendant_id = (int)($_POST['attendant_id'] ?? 0);
$attendant_name = trim($_POST['attendant_name'] ?? '');
$attendant_id_number = trim($_POST['attendant_id_number'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$experience_years = (int)($_POST['experience_years'] ?? 0);
$salary = (int)($_POST['salary'] ?? 0);
$address = trim($_POST['address'] ?? '');
$status = $_POST['status'] ?? 'active';
$gender = $_POST['gender'] ?? 'male';

// ভ্যালিডেশন
$errors = [];

if ($attendant_id <= 0) {
    $errors[] = "অবৈধ মহিলা পরিচারিকা ID";
}

if (empty($attendant_name)) {
    $errors[] = "মহিলা পরিচারিকাের নাম প্রয়োজন";
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
    header('Location: attendant_list.php');
    exit();
}

try {
    // মহিলা পরিচারিকা আইডি ইউনিক চেক করুন (অন্য মহিলা পরিচারিকার জন্য)
    $check_attendant = "SELECT COUNT(*) FROM attendants WHERE attendant_id_number = :attendant_id_number AND id != :id";
    $stmt = $pdo->prepare($check_attendant);
    $stmt->execute([':attendant_id_number' => $attendant_id_number, ':id' => $attendant_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই মহিলা পরিচারিকা আইডি ইতিমধ্যে অন্য মহিলা পরিচারিকার জন্য ব্যবহার করা হয়েছে";
        header('Location: edit_attendant.php?id=' . $attendant_id);
        exit();
    }
    
    // মহিলা পরিচারিকা আপডেট করুন
    $update_attendant = "
    UPDATE attendants 
    SET attendant_name = :attendant_name, 
        attendant_id_number = :attendant_id_number, 
        phone = :phone, 
        email = :email, 
        experience_years = :experience_years, 
        salary = :salary, 
        address = :address, 
        status = :status, 
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
    ";
    
    $stmt = $pdo->prepare($update_attendant);
    $result = $stmt->execute([
        ':attendant_name' => $attendant_name,
        ':attendant_id_number' => $attendant_id_number,
        ':phone' => $phone,
        ':email' => $email,
        ':salary' => $salary,
        ':address' => $address,
        ':status' => $status,
        ':id' => $attendant_id
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "মহিলা পরিচারিকা সফলভাবে আপডেট করা হয়েছে!";
        header('Location: attendant_list.php');
        exit();
    } else {
        $_SESSION['error_message'] = "মহিলা পরিচারিকা আপডেট করতে সমস্যা হয়েছে";
        header('Location: edit_attendant.php?id=' . $attendant_id);
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: edit_attendant.php?id=' . $attendant_id);
    exit();
}
?>
