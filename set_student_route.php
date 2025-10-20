<?php
require_once './config/database.php';
// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
// GET প্যারামিটার থেকে শিক্ষার্থী এবং রুট আইডি সংগ্রহ করুন
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$route_id = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;

// যদি শিক্ষার্থী এবং রুট আইডি বৈধ হয় তবে ডাটাবেসে আপডেট করুন
if ($student_id <= 0 || $route_id <= 0) {
    header('Location: student_list.php');
    exit();
}
// ডাটাবেসে সংযোগ এবং আপডেট কার্যক্রম
try {
    $stmt = "SELECT * FROM students WHERE id = :student_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        header('Location: student_list.php');
        exit();
    }
    $stmt = "SELECT * FROM route_sub_destinations WHERE route_id = :route_id AND destination_name = :destination_name LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['route_id' => $route_id, 'destination_name' => $student['pickup_location']]);
    $sub_route = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sub_route) {
        header('Location: student_list.php');
        exit();
    } else {
        $sub_route_id = $sub_route['id'];
    }

    $update_sql = "UPDATE students SET route_id = :route_id, sub_route_id = :sub_route_id WHERE id = :student_id";

} catch (PDOException $e) {
    die("ডাটাবেস সংযোগ ব্যর্থ: " . $e->getMessage());
}