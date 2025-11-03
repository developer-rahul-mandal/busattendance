<?php
require_once 'config/database.php';

try {
    // নিশ্চিত করুন ইনভয়েস টেবিল আছে
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

    // আজকের তারিখের দিন
    $today = date('d');

    // আজকের দিনে তৈরি হওয়া ইনভয়েসগুলির ছাত্রদের বের করুন
    $stmt = $pdo->prepare("
        SELECT i.student_id, i.amount, i.payment_status, i.status, i.invoice_date
        FROM invoices AS i
        JOIN students AS s ON i.student_id = s.student_id
        WHERE DAY(i.invoice_date) = :today
          AND s.status = 'active'  -- শুধুমাত্র Active ছাত্রদের জন্য
        ORDER BY i.invoice_date DESC
    ");
    $stmt->execute([':today' => $today]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertQuery = $pdo->prepare("
        INSERT INTO invoices (student_id, invoice_date, amount, payment_status, status)
        VALUES (:student_id, :invoice_date, :amount, :payment_status, :status)
    ");

    $createdCount = 0;
    foreach ($invoices as $invoice) {
        $nextMonthDate = date('Y-m-d', strtotime($invoice['invoice_date'] . ' +30 days'));

        // ডুপ্লিকেট চেক করুন (যদি ওই মাসে ইনভয়েস আগেই তৈরি হয়ে থাকে)
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM invoices
            WHERE student_id = :student_id AND invoice_date = :invoice_date
        ");
        $checkStmt->execute([
            ':student_id' => $invoice['student_id'],
            ':invoice_date' => $nextMonthDate
        ]);
        if ($checkStmt->fetchColumn() > 0) {
            continue; // ইতিমধ্যে তৈরি হলে স্কিপ করুন
        }

        // নতুন ইনভয়েস তৈরি করুন
        $insertQuery->execute([
            ':student_id' => $invoice['student_id'],
            ':invoice_date' => $nextMonthDate,
            ':amount' => $invoice['amount'],
            ':payment_status' => 'unpaid',
            ':status' => $invoice['status']
        ]);

        $createdCount++;
    }

    echo "✅ $createdCount new invoices created for active students on " . date('F Y') . "\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
