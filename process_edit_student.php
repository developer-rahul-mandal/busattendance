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
    header('Location: student_list.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$id = (int)trim($_POST['id']?? 0);
$student_name = trim($_POST['student_name'] ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$route_id = trim($_POST['route'] ?? '');
$sub_route_id = trim($_POST['sub_route'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$guardian_phone = trim($_POST['guardian_phone'] ?? '');
$father_name = trim($_POST['father_name'] ?? '');
$mother_name = trim($_POST['mother_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$gender = $_POST['gender'] ?? 'male';
$status = $_POST['status'] ?? 'active';

// ভ্যালিডেশন
$errors = [];

if ($id <= 0) {
    $errors[] = "অবৈধ শিক্ষার্থী ID";
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
    header('Location: edit_student.php?id=' . $id);
    exit();
}

try {
    
    // শিক্ষার্থী ID ইউনিক চেক করুন
    $check_student = "SELECT COUNT(*) FROM students WHERE student_id = :student_id AND id != :id";
    $stmt = $pdo->prepare($check_student);
    $stmt->execute([':student_id' => $student_id,':id' => $id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "এই শিক্ষার্থী ID ইতিমধ্যে অন্য শিক্ষার্থীর জন্য ব্যবহার করা হয়েছে";
        header('Location: edit_student.php?id=' . $id);
        exit();
    }
    
    // শিক্ষার্থী যোগ করুন
    $update_student = "
    UPDATE students 
    SET 
        student_name = :student_name,
        route_id = :route_id,
        sub_route_id = :sub_route_id,
        phone = :phone,
        guardian_phone = :guardian_phone,
        father_name = :father_name,
        mother_name = :mother_name,
        address = :address,
        gender = :gender,
        status = :status
    WHERE id = :id
";

$stmt = $pdo->prepare($update_student);
$result = $stmt->execute([
    ':id' => $id,
    ':student_name' => $student_name,
    ':route_id' => $route_id,
    ':sub_route_id' => $sub_route_id,
    ':phone' => $phone,
    ':guardian_phone' => $guardian_phone,
    ':father_name' => $father_name,
    ':mother_name' => $mother_name,
    ':address' => $address,
    ':gender' => $gender,
    ':status' => $status
]);

if ($result) {
    $_SESSION['success_message'] = "শিক্ষার্থীর তথ্য সফলভাবে আপডেট হয়েছে!";
    header('Location: student_list.php');
    exit();
} else {
    $_SESSION['error_message'] = "শিক্ষার্থীর তথ্য আপডেট করতে সমস্যা হয়েছে";
    header('Location: edit_student.php?id=' . $id);
    exit();
}

    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: edit_student.php?id=' . $id);
    exit();
}
?>
