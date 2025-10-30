<?php
session_start();
require_once 'config/database.php';

// POST রিকোয়েস্ট চেক করুন
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student_public_registration.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$student_name = trim($_POST['student_name'] ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$school_name = trim($_POST['school_name'] ?? '');
$sub_route = trim($_POST['sub_route'] ?? '');
$drop = trim($_POST['drop'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$guardian_phone = trim($_POST['guardian_phone'] ?? '');
$father_name = trim($_POST['father_name'] ?? '');
$mother_name = trim($_POST['mother_name'] ?? '');
$father_occupation = trim($_POST['father_occupation'] ?? '');
$mother_occupation = trim($_POST['mother_occupation'] ?? '');
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
    $errors[] = "Student photo required";
}


if (empty($student_name)) {
    $errors[] = "Student's name is required";
}

if (empty($student_id)) {
    $errors[] = "Student ID required";
}


if (empty($sub_route)) {
    $errors[] = "Student pickup location required";
}

if (empty($phone)) {
    $errors[] = "Phone number required";
}

if (empty($address)) {
    $errors[] = "Address required";
}

// ফোন নম্বর ভ্যালিডেশন
if (!empty($phone) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
    $errors[] = "Enter the correct phone number";
}

if (!empty($guardian_phone) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $guardian_phone)) {
    $errors[] = "Provide the correct guardian's phone number.";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: student_public_registration.php');
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
        school_name VARCHAR(255) DEFAULT NULL,
        route_id INT NOT NULL,
        sub_route_id INT NOT NULL,
        pickup_location VARCHAR(255),
        drop_location VARCHAR(255),
        phone VARCHAR(20) NOT NULL,
        guardian_phone VARCHAR(20),
        father_name VARCHAR(100),
        mother_name VARCHAR(100),
        father_occupation VARCHAR(100),
        mother_occupation VARCHAR(100),
        address TEXT NOT NULL,
        gender ENUM('male', 'female') DEFAULT 'male',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_student_table);
    
    // শিক্ষার্থী ID ইউনিক চেক করুন
    $check_student = "SELECT COUNT(*) FROM students WHERE student_name = :student_name AND phone = :phone";
    $stmt = $pdo->prepare($check_student);
    $stmt->execute([':student_name' => $student_name, ':phone' => $phone]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "This student already exists";
        header('Location: student_public_registration.php');
        exit();
    }

    // শিক্ষার্থী ID ইউনিক চেক করুন
    $check_student = "SELECT COUNT(*) FROM students WHERE student_id = :student_id";
    $stmt = $pdo->prepare($check_student);
    $stmt->execute([':student_id' => $student_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "This student ID already exists. If not Please try again.";
        header('Location: student_public_registration.php');
        exit();
    }
    
    // শিক্ষার্থী যোগ করুন
    $insert_student = "
    INSERT INTO students (student_id, student_name, img_path, school_name, pickup_location, drop_location, phone, guardian_phone, father_name, mother_name, father_occupation, mother_occupation, address, gender, status) 
    VALUES (:student_id, :student_name, :img_path, :school_name, :pickup_location, :drop_location, :phone, :guardian_phone, :father_name, :mother_name, :father_occupation, :mother_occupation, :address, :gender, :status)
    ";
    
    $stmt = $pdo->prepare($insert_student);
    $result = $stmt->execute([
        ':student_id' => $student_id,
        ':student_name' => $student_name,
        ':img_path' => $file_name,
        ':school_name' => $school_name,
        ':pickup_location' => $sub_route,
        ':drop_location' => $drop,
        ':phone' => $phone,
        ':guardian_phone' => $guardian_phone,
        ':father_name' => $father_name,
        ':mother_name' => $mother_name,
        ':father_occupation' => $father_occupation,
        ':mother_occupation' => $mother_occupation,
        ':address' => $address,
        ':gender' => $gender,
        ':status' => $status
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "Student added successfully!";
        header('Location: student_public_registration.php');
        exit();
    } else {
        $_SESSION['error_message'] = "There was a problem adding students.";
        header('Location: student_public_registration.php');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error Occurred: " . $e->getMessage();
    header('Location: student_public_registration.php');
    exit();
}
?>
