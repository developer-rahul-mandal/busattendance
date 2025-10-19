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
    header('Location: route_attendant_list.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$id = (int)($_POST['id'] ?? 0);
$way = trim($_POST['way'] ?? '');
$attendant = (int)trim($_POST['attendant'] ?? 0);
$bus = (int)($_POST['bus'] ?? 0);
$route = (int)trim($_POST['route'] ?? 0);
$driver = (int)trim($_POST['driver'] ?? 0);
$status = $_POST['status'] ?? 'active';

// ভ্যালিডেশন
$errors = [];

if ($id <= 0) {
    $errors[] = "অবৈধ  ID";
}

if (empty($way) || ($way !== 'to_go' && $way !== 'to_come')) {
    $errors[] = "পথ নির্বাচন করুন";
}

if (empty($attendant) || $attendant <= 0) {
    $errors[] = "মহিলা পরিচারিকা নির্বাচন করুন";
}

if (empty($bus) || $bus <= 0) {
    $errors[] = "বাস নির্বাচন করুন";
}

if (empty($route) || $route <= 0) {
    $errors[] = "রুট নির্বাচন করুন";
}

if (empty($driver) || $driver <= 0) {
    $errors[] = "ড্রাইভার নির্বাচন করুন";
}

if (!in_array($status, ['active', 'inactive'])) {
    $errors[] = "ভুল স্ট্যাটাস নির্বাচন করা হয়েছে";
}
// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: edit_route_attendant.php?id=' . $id);
    exit();
}

try {
    // শিক্ষার্থী যোগ করুন
    $update_route_attendant = "
    UPDATE route_attendant 
    SET 
        way = :way,
        attendant = :attendant,
        bus = :bus,
        route = :route,
        driver = :driver,
        status = :status
    WHERE id = :id
";

$stmt = $pdo->prepare($update_route_attendant);
$result = $stmt->execute([
    ':id' => $id,
    ':way' => $way,
    ':attendant' => $attendant,
    ':bus' => $bus,
    ':route' => $route,
    ':driver' => $driver,
    ':status' => $status
]);

if ($result) {
    $_SESSION['success_message'] = "রেকর্ড সফলভাবে আপডেট হয়েছে!";
    header('Location: route_attendant_list.php');
    exit();
} else {
    $_SESSION['error_message'] = "রেকর্ড আপডেট করতে সমস্যা হয়েছে";
    header('Location: edit_route_attendant.php?id=' . $id);
    exit();
}

    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: edit_route_attendant.php?id=' . $id);
    exit();
}
?>
