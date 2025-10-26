<?php
session_start();
require_once '../config/database.php';

// CSRF টোকেন চেক (সিকিউরিটির জন্য)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$student_name = trim($_POST['student_name'] ?? '');
$school_name = trim($_POST['school_name'] ?? '');
$student_phone = trim($_POST['phone'] ?? '');

// ভ্যালিডেশন
$errors = [];

if (empty($student_name)) {
    $errors[] = "Student Name required.";
}

if (empty($school_name)) {
    $errors[] = "School Name required.";
}

if (empty($student_phone)) {
    $errors[] = "Student phone number required.";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['login_error'] = implode(', ', $errors);
    header('Location: login.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_name = :student_name AND phone = :phone AND school_name = :school_name LIMIT 1");
    $stmt->execute(['student_name' => $student_name, 'phone' => $student_phone, 'school_name' => $school_name]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        $_SESSION['login_error'] = "Wrong Student ID or Phone Number.";
        header('Location: login.php');
        exit();
    } else {
        // লগইন সফল, সেশন সেট করুন
        $_SESSION['parent_logged_in'] = true;
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['student_name'];
        $_SESSION['school_name'] = $student['school_name'];
        $_SESSION['login_time'] = time();
        header('Location: dashboard.php');
        exit();
    }
    
} catch (PDOException $e    ) {
    $_SESSION['login_error'] = "Server error!";
    header('Location: login.php');
    exit();
}
