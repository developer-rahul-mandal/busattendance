<?php
session_start();
require_once 'config/database.php';

// CSRF টোকেন চেক (সিকিউরিটির জন্য)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// ফর্ম ডেটা সংগ্রহ করুন
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// ভ্যালিডেশন
$errors = [];

if (empty($username)) {
    $errors[] = "ইউজারনেম প্রয়োজন";
}

if (empty($password)) {
    $errors[] = "পাসওয়ার্ড প্রয়োজন";
}

// যদি কোনো ভ্যালিডেশন এরর থাকে
if (!empty($errors)) {
    $_SESSION['login_error'] = implode(', ', $errors);
    header('Location: login.php');
    exit();
}

try {
    // প্রথমে টেবিল আছে কিনা চেক করুন
    $table_check = $pdo->query("SHOW TABLES LIKE 'super_admins'")->fetch();
    if (!$table_check) {
        $_SESSION['login_error'] = "ডেটাবেস সেটআপ করা হয়নি। দয়া করে setup_database.php চালান।";
        header('Location: login.php');
        exit();
    }
    
    // রেকর্ড আছে কিনা চেক করুন
    $count = $pdo->query("SELECT COUNT(*) FROM super_admins")->fetchColumn();
    if ($count == 0) {
        $_SESSION['login_error'] = "কোনো অ্যাডমিন অ্যাকাউন্ট নেই। দয়া করে setup_database.php চালান।";
        header('Location: login.php');
        exit();
    }
    
    // ইউজার খুঁজে বের করুন
    $sql = "SELECT id, username, email, password, full_name FROM super_admins WHERE username = :username OR email = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    
    $user = $stmt->fetch();
    
    if ($user) {
        // পাসওয়ার্ড চেক করুন
        if (password_verify($password, $user['password'])) {
            // সফল লগইন
            $_SESSION['super_admin_logged_in'] = true;
            $_SESSION['super_admin_id'] = $user['id'];
            $_SESSION['super_admin_username'] = $user['username'];
            $_SESSION['super_admin_name'] = $user['full_name'];
            $_SESSION['super_admin_email'] = $user['email'];
            
            // লগইন টাইম সেভ করুন
            $_SESSION['login_time'] = time();
            
            // সফল লগইনের পর ড্যাশবোর্ডে রিডাইরেক্ট করুন
            header('Location: dashboard.php');
            exit();
        } else {
            // ভুল পাসওয়ার্ড
            $_SESSION['login_error'] = "ভুল পাসওয়ার্ড। সঠিক পাসওয়ার্ড দিন।";
            header('Location: login.php');
            exit();
        }
    } else {
        // ইউজার নেই
        $_SESSION['login_error'] = "এই ইউজারনেমের কোনো অ্যাকাউন্ট নেই।";
        header('Location: login.php');
        exit();
    }
    
} catch(PDOException $e) {
    // ডেটাবেস এরর
    $_SESSION['login_error'] = "সিস্টেমে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।";
    header('Location: login.php');
    exit();
}
?>