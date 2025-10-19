<?php
// সম্পূর্ণ ডেটাবেস রিসেট স্ক্রিপ্ট
require_once 'config/database.php';

echo "<h2>🔄 ডেটাবেস রিসেট - Bus Attendance System</h2>";

try {
    // 1. টেবিল ড্রপ করুন
    echo "<h3>1. বিদ্যমান টেবিল ড্রপ করা হচ্ছে...</h3>";
    $drop_sql = "DROP TABLE IF EXISTS super_admins";
    $pdo->exec($drop_sql);
    echo "✅ super_admins টেবিল ড্রপ করা হয়েছে<br>";
    
    // 2. নতুন টেবিল তৈরি করুন
    echo "<h3>2. নতুন টেবিল তৈরি করা হচ্ছে...</h3>";
    $create_table_sql = "
    CREATE TABLE super_admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_table_sql);
    echo "✅ নতুন super_admins টেবিল তৈরি হয়েছে<br>";
    
    // 3. ডিফল্ট অ্যাডমিন তৈরি করুন
    echo "<h3>3. ডিফল্ট অ্যাডমিন তৈরি করা হচ্ছে...</h3>";
    $default_password = 'admin123';
    $password_hash = password_hash($default_password, PASSWORD_DEFAULT);
    
    echo "পাসওয়ার্ড হ্যাশ তৈরি: " . substr($password_hash, 0, 20) . "...<br>";
    
    $insert_admin_sql = "
    INSERT INTO super_admins (username, email, password, full_name) 
    VALUES ('admin', 'admin@example.com', :password, 'সুপার অ্যাডমিন')
    ";
    
    $stmt = $pdo->prepare($insert_admin_sql);
    $result = $stmt->execute([':password' => $password_hash]);
    
    if ($result) {
        echo "✅ ডিফল্ট অ্যাডমিন তৈরি হয়েছে<br>";
    } else {
        echo "❌ অ্যাডমিন তৈরি ব্যর্থ<br>";
    }
    
    // 4. টেস্ট করুন
    echo "<h3>4. টেস্ট করা হচ্ছে...</h3>";
    
    // রেকর্ড সংখ্যা চেক
    $count = $pdo->query("SELECT COUNT(*) FROM super_admins")->fetchColumn();
    echo "মোট রেকর্ড: $count<br>";
    
    // অ্যাডমিন তথ্য দেখুন
    $admin = $pdo->query("SELECT id, username, email, full_name FROM super_admins WHERE username = 'admin'")->fetch();
    if ($admin) {
        echo "✅ admin ইউজার পাওয়া গেছে<br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Full Name: " . $admin['full_name'] . "<br>";
    }
    
    // পাসওয়ার্ড ভেরিফাই টেস্ট
    $stored_hash = $pdo->query("SELECT password FROM super_admins WHERE username = 'admin'")->fetchColumn();
    $verify_result = password_verify($default_password, $stored_hash);
    echo "পাসওয়ার্ড ভেরিফাই: " . ($verify_result ? "✅ সফল" : "❌ ব্যর্থ") . "<br>";
    
    // 5. সাফল্য মেসেজ
    if ($verify_result && $count > 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #c3e6cb;'>";
        echo "<h3>🎉 ডেটাবেস রিসেট সম্পূর্ণ!</h3>";
        echo "<p><strong>সব কিছু সফলভাবে রিসেট করা হয়েছে।</strong></p>";
        echo "<hr style='margin: 15px 0;'>";
        echo "<h4>📋 লগইন তথ্য:</h4>";
        echo "<table style='background: white; border-collapse: collapse; width: 100%;'>";
        echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>ইউজারনেম:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>admin</td></tr>";
        echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>পাসওয়ার্ড:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>admin123</td></tr>";
        echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>ইমেইল:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>admin@example.com</td></tr>";
        echo "</table>";
        echo "<br>";
        echo "<a href='login.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>এখন লগইন করুন</a>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #f5c6cb;'>";
        echo "<h3>❌ ডেটাবেস রিসেট ব্যর্থ</h3>";
        echo "<p>কিছু সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #f5c6cb;'>";
    echo "<h3>❌ ত্রুটি</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔗 দরকারী লিঙ্ক</h3>";
echo "<a href='debug_login.php' style='background: #6c757d; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>ডিবাগ পেজ</a>";
echo "<a href='test_system.php' style='background: #17a2b8; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>সিস্টেম টেস্ট</a>";
echo "<a href='setup_database.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>সেটআপ পেজ</a>";

echo "<br><br><small>রিসেট সম্পন্ন - " . date('Y-m-d H:i:s') . "</small>";
?>
