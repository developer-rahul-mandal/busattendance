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
$student_id = trim($_POST['student_id'] ?? '');
$invoice_date = trim($_POST['invoice_date'] ?? '');
$amount = (int)trim($_POST['amount'] ?? '');
$payment_status = $_POST['payment_status'] ?? 'unpaid';
$status = $_POST['status'] ?? 'active';

// ভ্যালিডেশন
$errors = [];

if (empty($student_id)) {
    $errors[] = "ছাত্রের আইডি প্রয়োজন।";
}
if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
    $errors[] = "বৈধ পরিমাণ প্রয়োজন।";
}
if (empty($invoice_date)) {
    $errors[] = "চালানের তারিখ প্রয়োজন।";
}
if (!in_array($payment_status, ['paid', 'unpaid'])) {
    $errors[] = "অবৈধ অর্থপ্রদানের অবস্থা।";
}
if (!in_array($status, ['active', 'inactive'])) {
    $errors[] = "অবৈধ অবস্থা।";
}
if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: generate_invoice.php');
    exit();
}

try {
    $createinvoicesTableQuery = "CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        invoice_date DATE NOT NULL,
        amount INT NOT NULL,
        payment_status ENUM('paid', 'unpaid') NOT NULL,
        status ENUM('active', 'inactive') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($createinvoicesTableQuery);
    // ছাত্র আইডি বৈধ কিনা তা যাচাই করুন
    $stmt = $pdo->prepare("SELECT count(*) AS total FROM students WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['total'] == 0) {
        $_SESSION['error_message'] = "ছাত্রের আইডি বিদ্যমান নেই।";
        header('Location: generate_invoice.php');
        exit();
    }
    
    $insertInvoiceQuery = "INSERT INTO invoices (student_id, invoice_date, amount, payment_status, status) 
                           VALUES (:student_id, :invoice_date, :amount, :payment_status, :status)";
    $stmt = $pdo->prepare($insertInvoiceQuery);
    $stmt->execute([
        ':student_id' => $student_id,
        ':invoice_date' => $invoice_date,
        ':amount' => $amount,
        ':payment_status' => $payment_status,
        ':status' => $status
    ]);
    $_SESSION['success_message'] = "চালান সফলভাবে তৈরি হয়েছে।";
    header('Location: generate_invoice.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে ত্রুটি ঘটেছে।";
}