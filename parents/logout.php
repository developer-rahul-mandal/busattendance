<?php
session_start();

// সব সেশন ডেটা মুছে দিন
$_SESSION = array();

// সেশন কুকি মুছে দিন
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// সেশন ধ্বংস করুন
session_destroy();

// লগইন পেজে রিডাইরেক্ট করুন
header('Location: login.php');
exit();
?>
