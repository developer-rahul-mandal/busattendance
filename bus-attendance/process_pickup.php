<?php
require_once '../config/database.php';
session_start();
if (!isset($_SESSION['route_attendant_id']) || $_SESSION['attendant_logged_in'] !== true || !isset($_SESSION['date']) || $_SESSION['date'] !== date('Y-m-d')) {
    header('Location: login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pickup_student.php');
    exit();
}

// $uri = "student://?id=1&route_id=2";
// echo $uri;
$php_input = file_get_contents('php://input');
$data = json_decode($php_input, true);

$uri = trim($data['student_qr'] ?? '');
$queryString = explode('?', $uri)[1] ?? '';
parse_str($queryString ?? '', $query_params);
$student_id = trim($query_params['student_id'] ?? '');
$phone = trim($query_params['phone'] ?? '');
$route_attendant_id = (int)trim($_SESSION['route_attendant_id'] ?? 0);
if (empty($student_id) || empty($phone) || $route_attendant_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ভুল QR কোড স্ক্যান করা হয়েছে।']);
    exit();
}
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id AND phone = :phone LIMIT 1");
    $stmt->execute(['student_id' => $student_id, 'phone' => $phone]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'এই ছাত্রের তথ্য পাওয়া যায়নি।']);
        exit();
    }
    // পিকআপ টেবিল তৈরি করুন (যদি না থাকে)
    $createTableSQL = "CREATE TABLE IF NOT EXISTS pickup_and_drop (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    route_attendant_id INT NOT NULL,
    pickup_time DATETIME NOT NULL,
    drop_time DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (route_attendant_id) REFERENCES route_attendant(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";


    $pdo->exec($createTableSQL);
    
    // // ডাটাবেস থেকে ছাত্র তথ্য যাচাই করুন
    // $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM students WHERE student_id = :student_id AND route_id = :route_id");
    // $stmt->execute(['student_id' => $student_id, 'route_id' => $route_id]);
    // $student = $stmt->fetch(PDO::FETCH_ASSOC);
    // if ($student['total'] == 0) {
    //     http_response_code(404);
    //     echo json_encode(['status' => 'error', 'message' => 'এই ছাত্রের তথ্য পাওয়া যায়নি।']);
    //     exit();
    // }
    
    // চেক করুন যে ছাত্রটি ইতিমধ্যেই পিকআপ হয়েছে কিনা
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pickup_and_drop WHERE student_id = :student_id AND route_attendant_id = :route_attendant_id AND DATE(pickup_time) = CURDATE() AND drop_time IS NULL");
    $stmt->execute(['student_id' => $student['id'], 'route_attendant_id' => $route_attendant_id]);
    $pickup = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pickup['total'] > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'এই ছাত্রটি ইতিমধ্যেই আজ পিকআপ হয়েছে।']);
        exit();
    }

    // পিকআপ তথ্য সেভ করুন
    $stmt = $pdo->prepare("INSERT INTO pickup_and_drop (student_id, route_attendant_id, pickup_time) VALUES (:student_id, :route_attendant_id, NOW())");
    $stmt->execute(['student_id' => $student["id"], 'route_attendant_id' => $route_attendant_id]);
    echo json_encode(['status' => 'success', 'message' => 'ছাত্র পিকআপ সফল হয়েছে।']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'সিস্টেমে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।']);
    exit();
}