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
    header('Location: add_route_attendant.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$way = trim($_POST['way'] ?? '');
$attendant = (int)trim($_POST['attendant'] ?? 0);
$bus = (int)($_POST['bus'] ?? 0);
$route = (int)trim($_POST['route'] ?? 0);
$driver = (int)trim($_POST['driver'] ?? 0);
$status = $_POST['status'] ?? 'active';

// ভ্যালিডেশন
$errors = [];

if (empty($way) || ($way !== 'to_go' && $way !== 'to_come')) {
    $errors[] = "পথ নির্বাচন প্রয়োজন";
}

if ($attendant <= 0) {
    $errors[] = "অ্যাটেনডেন্ট নির্বাচন প্রয়োজন";
}

if ($bus <= 0) {
    $errors[] = "বাস নির্বাচন প্রয়োজন";
}

if ($route <= 0) {
    $errors[] = "রুট নির্বাচন প্রয়োজন";
}

if ($driver <= 0) {
    $errors[] = "ড্রাইভার নির্বাচন প্রয়োজন";
}

if (!in_array($status, ['active', 'inactive'])) {
    $errors[] = "ভুল স্ট্যাটাস নির্বাচন করা হয়েছে";
}


// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: add_route_attendant.php');
    exit();
}

try {
    // প্রথমে বাস টেবিল তৈরি করুন (যদি না থাকে)
    $create_route_attendant_table = "
    CREATE TABLE IF NOT EXISTS route_attendant (
        id INT AUTO_INCREMENT PRIMARY KEY,
        way VARCHAR(100),
        attendant INT,
        bus INT,
        route INT,
        driver INT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        -- Foreign key constraints
        FOREIGN KEY (attendant) REFERENCES attendants(id),
        FOREIGN KEY (bus) REFERENCES buses(id),
        FOREIGN KEY (route) REFERENCES routes(id),
        FOREIGN KEY (driver) REFERENCES drivers(id)
    ) ENGINE=InnoDB;
";

    
    $pdo->exec($create_route_attendant_table);
    
    // বাস যোগ করুন
    $insert_bus = "
    INSERT INTO route_attendant (way, attendant, bus, route, driver, status) 
    VALUES (:way, :attendant, :bus, :route, :driver, :status)
    ";
    
    $stmt = $pdo->prepare($insert_bus);
    $result = $stmt->execute([
        ':way' => $way,
        ':attendant' => $attendant,
        ':bus' => $bus,
        ':route' => $route,
        ':driver' => $driver,
        ':status' => $status
    ]);
    
    if ($result) {
        $_SESSION['success_message'] = "রুট পরিচারিকা সফলভাবে যোগ করা হয়েছে!";
        header('Location: add_route_attendant.php');
        exit();
    } else {
        $_SESSION['error_message'] = "রুট পরিচারিকা যোগ করতে সমস্যা হয়েছে";
        header('Location: add_route_attendant.php');
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে: " . $e->getMessage();
    header('Location: add_route_attendant.php');
    exit();
}
?>
