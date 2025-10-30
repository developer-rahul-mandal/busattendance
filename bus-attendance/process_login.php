<?php
session_start();
require_once '../config/database.php';

// CSRF টোকেন চেক (সিকিউরিটির জন্য)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$route_id = (int)trim($_POST['route'] ?? '');
$bus_id = (int)trim($_POST['bus'] ?? '');
$way = trim($_POST['way'] ?? '');
// ভ্যালিডেশন
$errors = [];

if (empty($route_id) || $route_id <= 0) {
    $errors[] = "রুট প্রয়োজন";
}

if (empty($bus_id) || $bus_id <= 0) {
    $errors[] = "বাস নম্বর প্রয়োজন";
}

if (empty($way) || !in_array($way, ['to_go', 'to_come'])) {
    $errors[] = "সঠিক যাত্রার দিক নির্বাচন করুন।";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['login_error'] = implode(', ', $errors);
    header('Location: login.php');
    exit();
}

try {
    // ডাটাবেস থেকে বাস তথ্য যাচাই করুন
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM routes WHERE id = :id");
    $stmt->execute(['id' => $route_id]);
    $route = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($route['total'] == 0) {
        $_SESSION['login_error'] = "ভুল রুট নির্বাচন করা হয়েছে।";
        header('Location: login.php');
        exit();
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM buses WHERE id = :id");
    $stmt->execute(['id' => $bus_id]);
    $bus = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($bus['total'] == 0) {
        $_SESSION['login_error'] = "ভুল বাস নম্বর প্রদান করা হয়েছে।";
        header('Location: login.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM route_attendant WHERE way = :way AND route = :route_id AND bus = :bus_id AND DATE(created_at) = CURDATE() LIMIT 1");
    $stmt->execute(['way' => $way,'route_id' => $route_id, 'bus_id' => $bus_id]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$attendance) {
        $_SESSION['login_error'] = "আজ এরকম কোনো সংযোগ পাওয়া যায়নি।";
        header('Location: login.php');
        exit();
    } else {
        // সফল লগইন, সেশন সেট করুন
        $_SESSION['route_attendant_id'] = $attendance['id'];
        $_SESSION['attendant_logged_in'] = true;
        $_SESSION['date'] = date('Y-m-d', strtotime($attendance['created_at']));

        // লগইন টাইম সেভ করুন
        $_SESSION['login_time'] = time();
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e    ) {
    $_SESSION['login_error'] = "সিস্টেমে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।";
    header('Location: login.php');
    exit();
}
