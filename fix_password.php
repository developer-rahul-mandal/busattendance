<?php
// পাসওয়ার্ড ফিক্স স্ক্রিপ্ট
require_once 'config/database.php';

echo "<h2>🔧 পাসওয়ার্ড ফিক্স - Bus Attendance System</h2>";

try {
    // প্রথমে বিদ্যমান অ্যাডমিনের তথ্য দেখুন
    echo "<h3>1. বিদ্যমান অ্যাডমিন তথ্য</h3>";
    $admin = $pdo->query("SELECT id, username, email, password, full_name FROM super_admins WHERE username = 'admin'")->fetch();
    
    if ($admin) {
        echo "✅ admin ইউজার পাওয়া গেছে<br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Full Name: " . $admin['full_name'] . "<br>";
        echo "Current Hash: " . $admin['password'] . "<br>";
    } else {
        echo "❌ admin ইউজার পাওয়া যায়নি<br>";
        exit();
    }
    
    // নতুন পাসওয়ার্ড হ্যাশ তৈরি করুন
    echo "<h3>2. নতুন পাসওয়ার্ড হ্যাশ তৈরি</h3>";
    $new_password = 'admin123';
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    echo "নতুন পাসওয়ার্ড: $new_password<br>";
    echo "নতুন হ্যাশ: $new_hash<br>";
    
    // পাসওয়ার্ড আপডেট করুন
    echo "<h3>3. পাসওয়ার্ড আপডেট</h3>";
    $update_sql = "UPDATE super_admins SET password = :password WHERE username = 'admin'";
    $stmt = $pdo->prepare($update_sql);
    $result = $stmt->execute([':password' => $new_hash]);
    
    if ($result) {
        echo "✅ পাসওয়ার্ড সফলভাবে আপডেট হয়েছে<br>";
    } else {
        echo "❌ পাসওয়ার্ড আপডেট ব্যর্থ<br>";
    }
    
    // আপডেটের পর টেস্ট করুন
    echo "<h3>4. আপডেট পরীক্ষা</h3>";
    $updated_admin = $pdo->query("SELECT password FROM super_admins WHERE username = 'admin'")->fetch();
    $test_verify = password_verify($new_password, $updated_admin['password']);
    
    echo "নতুন হ্যাশ: " . $updated_admin['password'] . "<br>";
    echo "পাসওয়ার্ড ভেরিফাই: " . ($test_verify ? "✅ সফল" : "❌ ব্যর্থ") . "<br>";
    
    if ($test_verify) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>🎉 পাসওয়ার্ড ফিক্স সম্পূর্ণ!</h4>";
        echo "<strong>লগইন তথ্য:</strong><br>";
        echo "ইউজারনেম: admin<br>";
        echo "পাসওয়ার্ড: admin123<br>";
        echo "<br>";
        echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>এখন লগইন করুন</a>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "❌ পাসওয়ার্ড ফিক্স ব্যর্থ। দয়া করে আবার চেষ্টা করুন।";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "❌ ত্রুটি: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔍 আরও ডিবাগ</h3>";
echo "<a href='debug_login.php' style='background: #6c757d; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>ডিবাগ পেজ</a>";
echo "<a href='test_system.php' style='background: #17a2b8; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>সিস্টেম টেস্ট</a>";
?>
