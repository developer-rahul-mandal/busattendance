<?php
// সহজ সিস্টেম টেস্ট
echo "<h2>🧪 সিস্টেম টেস্ট - Bus Attendance</h2>";

// 1. PHP ভার্সন চেক
echo "<h3>1. PHP ভার্সন</h3>";
echo "PHP ভার্সন: " . phpversion() . "<br>";

// 2. প্রয়োজনীয় এক্সটেনশন
echo "<h3>2. PHP এক্সটেনশন</h3>";
$extensions = ['pdo', 'pdo_mysql'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "✅" : "❌") . "<br>";
}

// 3. ডেটাবেস কানেকশন টেস্ট
echo "<h3>3. ডেটাবেস কানেকশন</h3>";
try {
    require_once 'config/database.php';
    echo "✅ ডেটাবেস কানেকশন সফল<br>";
    
    // টেবিল চেক
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('super_admins', $tables)) {
        echo "✅ super_admins টেবিল বিদ্যমান<br>";
        
        // রেকর্ড চেক
        $count = $pdo->query("SELECT COUNT(*) FROM super_admins")->fetchColumn();
        echo "মোট অ্যাডমিন: $count<br>";
        
        if ($count > 0) {
            $admin = $pdo->query("SELECT username, email FROM super_admins LIMIT 1")->fetch();
            echo "প্রথম অ্যাডমিন: " . $admin['username'] . " (" . $admin['email'] . ")<br>";
        }
    } else {
        echo "❌ super_admins টেবিল নেই<br>";
    }
    
} catch (Exception $e) {
    echo "❌ ডেটাবেস কানেকশন ব্যর্থ: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>📋 পরবর্তী পদক্ষেপ</h3>";

if (!isset($pdo) || !in_array('super_admins', $tables ?? [])) {
    echo "<p style='color: red;'>❌ ডেটাবেস সেটআপ প্রয়োজন</p>";
    echo "<a href='setup_database.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>ডেটাবেস সেটআপ করুন</a>";
} elseif (($count ?? 0) == 0) {
    echo "<p style='color: orange;'>⚠️ কোনো অ্যাডমিন অ্যাকাউন্ট নেই</p>";
    echo "<a href='setup_database.php' style='background: #ffc107; color: black; padding: 10px; text-decoration: none; border-radius: 5px;'>অ্যাডমিন অ্যাকাউন্ট তৈরি করুন</a>";
} else {
    echo "<p style='color: green;'>✅ সব কিছু ঠিক আছে!</p>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>লগইন করুন</a>";
}

echo "<br><br>";
echo "<a href='debug_login.php' style='background: #6c757d; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>বিস্তারিত ডিবাগ</a>";
?>
