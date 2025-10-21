<?php
session_start();
require_once './config/database.php';
// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
// GET প্যারামিটার থেকে শিক্ষার্থী এবং সাব-রুট আইডি সংগ্রহ করুন
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$sub_route_id = isset($_GET['sub_route_id']) ? (int)$_GET['sub_route_id'] : 0;
// যদি শিক্ষার্থী এবং সাব-রুট আইডি বৈধ হয় তবে ডাটাবেসে আপডেট করুন
if ($student_id <= 0 || $sub_route_id <= 0) {
    $_SESSION['error_message'] = "অবৈধ শিক্ষার্থী বা সাব-রুট নির্বাচন!";
    header('Location: student_list.php');
    exit();
}
// ডাটাবেসে সংযোগ এবং আপডেট কার্যক্রম
try {
    // শিক্ষার্থী তথ্য আনুন
    $stmt = "SELECT * FROM students WHERE id = :student_id LIMIT 1";
    $stmt = $pdo->prepare($stmt);
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        $_SESSION['error_message'] = "অবৈধ শিক্ষার্থী নির্বাচন!";
        header('Location: student_list.php');
        exit();
    }

    // সাব-রুট তথ্য আনুন
    $stmt = "SELECT * FROM route_sub_destinations WHERE id = :sub_route_id LIMIT 1";
    $stmt = $pdo->prepare($stmt);
    $stmt->execute(['sub_route_id' => $sub_route_id]);
    $sub_route = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sub_route) {
        $_SESSION['error_message'] = "অবৈধ সাব-রুট নির্বাচন!";
        header('Location: student_list.php');
        exit();
    }

    // শিক্ষার্থীর সাব-রুট চেক করুন
    if ($student['sub_route_id'] != 0) {
        $_SESSION['error_message'] = "শিক্ষার্থীর সাব-রুট ইতিমধ্যে সেট করা হয়েছে!";
        header('Location: student_list.php');
        exit();
    }

    // শিক্ষার্থীর সাব-রুট আপডেট করুন    
    $update_sql = "UPDATE students SET sub_route_id = :sub_route_id WHERE id = :student_id";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([
        ':sub_route_id' => $sub_route_id,
        ':student_id' => $student_id
    ]);
    $_SESSION['success_message'] = "শিক্ষার্থীর সাব-রুট সফলভাবে আপডেট হয়েছে!";
    header('Location: student_list.php');

} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে!";
    header('Location: student_list.php');
    exit();
}