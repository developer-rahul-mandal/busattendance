<?php
// ডেটাবেস সেটআপ স্ক্রিপ্ট
require_once 'config/database.php';

try {
    // সুপার অ্যাডমিন টেবিল তৈরি করুন
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS super_admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_table_sql);
    echo "সুপার অ্যাডমিন টেবিল তৈরি হয়েছে।<br>";
    
    // ডিফল্ট সুপার অ্যাডমিন তৈরি করুন (পাসওয়ার্ড: admin123)
    $default_password = 'admin123';
    $password_hash = password_hash($default_password, PASSWORD_DEFAULT);
    
    echo "পাসওয়ার্ড হ্যাশ তৈরি করা হয়েছে: " . substr($password_hash, 0, 20) . "...<br>";
    
    $check_admin_sql = "SELECT COUNT(*) FROM super_admins WHERE username = 'admin'";
    $stmt = $pdo->query($check_admin_sql);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $insert_admin_sql = "
        INSERT INTO super_admins (username, email, password, full_name) 
        VALUES ('admin', 'admin@example.com', :password, 'সুপার অ্যাডমিন')
        ";
        
        $stmt = $pdo->prepare($insert_admin_sql);
        $result = $stmt->execute([':password' => $password_hash]);
        
        if ($result) {
            echo "✅ ডিফল্ট সুপার অ্যাডমিন তৈরি হয়েছে।<br>";
            
            // পাসওয়ার্ড ভেরিফাই টেস্ট করুন
            $verify_test = password_verify($default_password, $password_hash);
            echo "পাসওয়ার্ড ভেরিফাই টেস্ট: " . ($verify_test ? "✅ সফল" : "❌ ব্যর্থ") . "<br>";
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>লগইন তথ্য:</strong><br>";
            echo "ইউজারনেম: <strong>admin</strong><br>";
            echo "পাসওয়ার্ড: <strong>admin123</strong><br>";
            echo "ইমেইল: admin@example.com<br>";
            echo "</div>";
        } else {
            echo "❌ অ্যাডমিন তৈরি ব্যর্থ<br>";
        }
    } else {
        echo "⚠️ ডিফল্ট সুপার অ্যাডমিন ইতিমধ্যে বিদ্যমান।<br>";
        
        // বিদ্যমান অ্যাডমিনের পাসওয়ার্ড আপডেট করুন
        $update_password_sql = "UPDATE super_admins SET password = :password WHERE username = 'admin'";
        $stmt = $pdo->prepare($update_password_sql);
        $update_result = $stmt->execute([':password' => $password_hash]);
        
        if ($update_result) {
            echo "✅ বিদ্যমান অ্যাডমিনের পাসওয়ার্ড আপডেট হয়েছে।<br>";
            
            // পাসওয়ার্ড ভেরিফাই টেস্ট করুন
            $verify_test = password_verify($default_password, $password_hash);
            echo "পাসওয়ার্ড ভেরিফাই টেস্ট: " . ($verify_test ? "✅ সফল" : "❌ ব্যর্থ") . "<br>";
        } else {
            echo "❌ পাসওয়ার্ড আপডেট ব্যর্থ<br>";
        }
        
        // বিদ্যমান অ্যাডমিনের তথ্য দেখান
        $existing_admin = $pdo->query("SELECT username, email, full_name FROM super_admins WHERE username = 'admin'")->fetch();
        if ($existing_admin) {
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>বিদ্যমান অ্যাডমিন তথ্য:</strong><br>";
            echo "ইউজারনেম: " . $existing_admin['username'] . "<br>";
            echo "ইমেইল: " . $existing_admin['email'] . "<br>";
            echo "নাম: " . $existing_admin['full_name'] . "<br>";
            echo "পাসওয়ার্ড: admin123 (নতুন হ্যাশ দিয়ে আপডেট করা হয়েছে)<br>";
            echo "</div>";
        }
    }
    
    echo "<br><a href='login.php'>লগইন পেজে যান</a>";
    
} catch(PDOException $e) {
    echo "সমস্যা: " . $e->getMessage();
}
?>