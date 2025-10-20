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
    header('Location: add_student.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$student_name = trim($_POST['student_name'] ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$route_id = trim($_POST['route'] ?? '');
$sub_route_id = trim($_POST['sub_route'] ?? '');
$sub_route = trim($_POST['sub_route_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$guardian_phone = trim($_POST['guardian_phone'] ?? '');
$father_name = trim($_POST['father_name'] ?? '');
$mother_name = trim($_POST['mother_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$gender = $_POST['gender'] ?? 'male';
$status = $_POST['status'] ?? 'active';

// আপলোড ডিরেক্টরি এবং বেস URL
$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ভ্যালিডেশন
$errors = [];

// ছবি আপলোড হ্যান্ডলিং
if (isset($_FILES["image"])) {
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_name = uniqid() . "_" . basename($_FILES['image']['name']);
    $path = $upload_dir . $file_name;
    move_uploaded_file($file_tmp, $path);
} else {
    $errors[] = "শিক্ষার্থীর ছবি প্রয়োজন।";
}


if (empty($student_name)) {
    $errors[] = "শিক্ষার্থীর নাম প্রয়োজন";
}

if (empty($student_id)) {
    $errors[] = "শিক্ষার্থী ID প্রয়োজন";
}

if (empty($route_id)) {
    $errors[] = "রুট নির্বাচন করুন";
}

if (empty($sub_route_id)) {
    $errors[] = "সাব-রুট নির্বাচন করুন";
}

if (empty($phone)) {
    $errors[] = "ফোন নম্বর প্রয়োজন";
}

if (empty($address)) {
    $errors[] = "ঠিকানা প্রয়োজন";
}

// ফোন নম্বর ভ্যালিডেশন
if (!empty($phone) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
    $errors[] = "সঠিক ফোন নম্বর দিন";
}

if (!empty($guardian_phone) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $guardian_phone)) {
    $errors[] = "সঠিক অভিভাবকের ফোন নম্বর দিন";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: add_student.php');
    exit();
}

try {
    // প্রথমে শিক্ষার্থী টেবিল তৈরি করুন (যদি না থাকে)
    $create_student_table = "
    CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        student_name VARCHAR(100) NOT NULL,
        img_path VARCHAR(255) DEFAULT NULL,
        route_id INT NOT NULL,
        sub_route_id INT NOT NULL,
        pickup_location VARCHAR(255),
        phone VARCHAR(20) NOT NULL,
        guardian_phone VARCHAR(20),
        father_name VARCHAR(100),
        mother_name VARCHAR(100),
        address TEXT NOT NULL,
        gender ENUM('male', 'female') DEFAULT 'male',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_student_table);
    
    // শিক্ষার্থী ID ইউনিক চেক করুন
    $check_student = "SELECT COUNT(*) FROM students WHERE student_id = :student_id";
    $stmt = $pdo->prepare($check_student);
    $stmt->execute([':student_id' => $student_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই শিক্ষার্থী ID ইতিমধ্যে বিদ্যমান";
        header('Location: add_student.php');
        exit();
    }
    
    // শিক্ষার্থী যোগ করুন
    $insert_student = "
    INSERT INTO students (student_id, student_name, img_path, route_id, sub_route_id, pickup_location, phone, guardian_phone, father_name, mother_name, address, gender, status) 
    VALUES (:student_id, :student_name, :img_path, :route_id, :sub_route_id, :pickup_location, :phone, :guardian_phone, :father_name, :mother_name, :address, :gender, :status)
    ";
    
    $stmt = $pdo->prepare($insert_student);
    $result = $stmt->execute([
        ':student_id' => $student_id,
        ':student_name' => $student_name,
        ':img_path' => $file_name,
        ':route_id' => $route_id,
        ':sub_route_id' => $sub_route_id,
        ':pickup_location' => $sub_route,
        ':phone' => $phone,
        ':guardian_phone' => $guardian_phone,
        ':father_name' => $father_name,
        ':mother_name' => $mother_name,
        ':address' => $address,
        ':gender' => $gender,
        ':status' => $status
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "শিক্ষার্থী সফলভাবে যোগ করা হয়েছে!";
        header('Location: add_student.php');
        exit();
    } else {
        $_SESSION['error_message'] = "শিক্ষার্থী যোগ করতে সমস্যা হয়েছে";
        header('Location: add_student.php');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: add_student.php');
    exit();
}
?>
