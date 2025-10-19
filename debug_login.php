<?php
session_start();
require_once 'config/database.php';

echo "<h2>🔍 লগইন ডিবাগ - Bus Attendance System</h2>";

// 1. ডেটাবেস কানেকশন চেক
echo "<h3>1. ডেটাবেস কানেকশন চেক</h3>";
try {
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "✅ MySQL কানেকশন সফল - ভার্সন: $version<br>";
} catch (Exception $e) {
    echo "❌ ডেটাবেস কানেকশন ব্যর্থ: " . $e->getMessage() . "<br>";
    exit();
}

// 2. টেবিল চেক
echo "<h3>2. টেবিল চেক</h3>";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('super_admins', $tables)) {
        echo "✅ super_admins টেবিল বিদ্যমান<br>";
        
        // টেবিল স্ট্রাকচার দেখুন
        echo "<h4>টেবিল স্ট্রাকচার:</h4>";
        $structure = $pdo->query("DESCRIBE super_admins")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($structure as $row) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "❌ super_admins টেবিল অনুপস্থিত<br>";
        echo "<p><a href='setup_database.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>ডেটাবেস সেটআপ করুন</a></p>";
        exit();
    }
} catch (Exception $e) {
    echo "❌ টেবিল চেক ব্যর্থ: " . $e->getMessage() . "<br>";
}

// 3. রেকর্ড চেক
echo "<h3>3. রেকর্ড চেক</h3>";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM super_admins")->fetchColumn();
    echo "মোট রেকর্ড সংখ্যা: $count<br>";
    
    if ($count > 0) {
        echo "<h4>বিদ্যমান রেকর্ড:</h4>";
        $users = $pdo->query("SELECT id, username, email, full_name, created_at FROM super_admins")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['full_name'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ কোনো রেকর্ড নেই<br>";
        echo "<p><a href='setup_database.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>ডেটাবেস সেটআপ করুন</a></p>";
    }
} catch (Exception $e) {
    echo "❌ রেকর্ড চেক ব্যর্থ: " . $e->getMessage() . "<br>";
}

// 4. পাসওয়ার্ড টেস্ট
echo "<h3>4. পাসওয়ার্ড টেস্ট</h3>";
try {
    $test_password = 'admin123';
    $test_hash = password_hash($test_password, PASSWORD_DEFAULT);
    echo "টেস্ট পাসওয়ার্ড: $test_password<br>";
    echo "টেস্ট হ্যাশ: $test_hash<br>";
    
    $verify_result = password_verify($test_password, $test_hash);
    echo "পাসওয়ার্ড ভেরিফাই রেজাল্ট: " . ($verify_result ? "✅ সফল" : "❌ ব্যর্থ") . "<br>";
    
    // ডেটাবেসের পাসওয়ার্ড চেক
    $admin_user = $pdo->query("SELECT password FROM super_admins WHERE username = 'admin'")->fetch();
    if ($admin_user) {
        $db_verify = password_verify($test_password, $admin_user['password']);
        echo "ডেটাবেস পাসওয়ার্ড ভেরিফাই: " . ($db_verify ? "✅ সফল" : "❌ ব্যর্থ") . "<br>";
    } else {
        echo "❌ admin ইউজার পাওয়া যায়নি<br>";
    }
    
} catch (Exception $e) {
    echo "❌ পাসওয়ার্ড টেস্ট ব্যর্থ: " . $e->getMessage() . "<br>";
}

// 5. ফর্ম ডেটা টেস্ট
echo "<h3>5. ফর্ম ডেটা টেস্ট</h3>";
if ($_POST) {
    echo "POST ডেটা পাওয়া গেছে:<br>";
    echo "Username: " . ($_POST['username'] ?? 'খালি') . "<br>";
    echo "Password: " . ($_POST['password'] ?? 'খালি') . "<br>";
    
    // লগইন প্রসেস টেস্ট
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        $sql = "SELECT id, username, email, password, full_name FROM super_admins WHERE username = :username OR email = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ ইউজার পাওয়া গেছে: " . $user['username'] . "<br>";
            $password_check = password_verify($password, $user['password']);
            echo "পাসওয়ার্ড চেক: " . ($password_check ? "✅ সফল" : "❌ ব্যর্থ") . "<br>";
            
            if ($password_check) {
                echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "🎉 লগইন সফল! আপনি ড্যাশবোর্ডে যেতে পারবেন।";
                echo "</div>";
            }
        } else {
            echo "❌ ইউজার পাওয়া যায়নি<br>";
        }
    } catch (Exception $e) {
        echo "❌ লগইন টেস্ট ব্যর্থ: " . $e->getMessage() . "<br>";
    }
} else {
    echo "কোনো POST ডেটা নেই। লগইন ফর্ম থেকে টেস্ট করুন।<br>";
}

echo "<hr>";
echo "<h3>লগইন ফর্ম টেস্ট</h3>";
?>

<form method="POST" action="debug_login.php" style="background: #f8f9fa; padding: 20px; border-radius: 10px; max-width: 400px;">
    <div style="margin-bottom: 15px;">
        <label>ইউজারনেম:</label><br>
        <input type="text" name="username" value="admin" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label>পাসওয়ার্ড:</label><br>
        <input type="password" name="password" value="admin123" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
    </div>
    
    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        টেস্ট লগইন
    </button>
</form>

<div style="margin-top: 20px;">
    <a href="setup_database.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">ডেটাবেস সেটআপ</a>
    <a href="login.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">লগইন পেজ</a>
</div>

<?php
echo "<br><small>ডিবাগ সম্পন্ন - " . date('Y-m-d H:i:s') . "</small>";
?>
