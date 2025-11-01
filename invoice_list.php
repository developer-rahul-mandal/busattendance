<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// শিক্ষার্থীর তালিকা আনুন
try {
    $students = $pdo->query("SELECT * FROM students ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>শিক্ষার্থীর তালিকা - বাস উপস্থিতি ব্যবস্থাপনা</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 30px;
            margin-top: 30px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
        }
        .badge-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .badge-danger {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        }
        .badge-info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }
    </style>
</head>
<body>
    <!-- নেভিগেশন বার -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-bus me-2"></i>
                বাস উপস্থিতি ব্যবস্থাপনা
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    স্বাগতম, <?php echo htmlspecialchars($_SESSION['super_admin_name']); ?>
                </span>
                <a href="dashboard.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-home me-1"></i>
                    ড্যাশবোর্ড
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    লগআউট
                </a>
            </div>
        </div>
    </nav>

    <div class="row mb-4" id="invoices">
            <div class="col-12">
                <div class="quick-actions">
                    <h4 class="mb-4">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        INVOICES
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">চালান নম্বর</th>
                                    <th scope="col">তারিখ</th>
                                    <th scope="col">পরিমাণ (INR)</th>
                                    <th scope="col">অর্থপ্রদানের অবস্থা</th>
                                    <th scope="col">অবস্থা</th>
                                    <th scope="col">কার্যক্রম</th>
                                </tr>
                            </thead>
                            <tbody id="invoice-table">
                                <?php
                                try {
                                    $stmt = $pdo->prepare("SELECT * FROM invoices ORDER BY created_at DESC");
                                    $stmt->execute();
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td># ' . htmlspecialchars($row['id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['invoice_date']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['amount']) . '</td>';
                                        echo '<td>' . ($row['payment_status'] == 'paid' ? '<span class="text-success">পরিশোধ করা হয়েছে</span>' : '<span class="text-danger">পরিশোধ করা হয়নি</span>') . '</td>';
                                        echo '<td>' . htmlspecialchars(ucfirst($row['status'])) . '</td>';
                                        echo '<td><button class="btn btn-sm btn-outline-danger" title="মুছে ফেলুন" onclick="deleteInvoice(' . $row['id'].')">
                                            <i class="fas fa-trash"></i>
                                        </button></td>';
                                        echo '</tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="5" class="text-center">Error fetching invoices.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function deleteInvoice(id) {
            if (confirm('আপনি কি চালানটি মুছে ফেলতে চান?')) {
                // AJAX দিয়ে ডিলিট রিকোয়েস্ট পাঠান
                fetch('delete_invoice.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('চালানটি সফলভাবে মুছে ফেলা হয়েছে!');
                        location.reload();
                    } else {
                        alert('চালানটি মুছতে সমস্যা হয়েছে: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('সমস্যা হয়েছে: ' + error);
                });
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
