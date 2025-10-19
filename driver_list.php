<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// ড্রাইভার তালিকা আনুন
try {
    $drivers = $pdo->query("SELECT * FROM drivers ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $drivers = [];
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্রাইভার তালিকা - বাস উপস্থিতি ব্যবস্থাপনা</title>
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

    <div class="container">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3><i class="fas fa-user-tie me-2"></i>ড্রাইভার তালিকা</h3>
                    <p class="text-muted">সকল ড্রাইভারের তালিকা এবং তথ্য</p>
                </div>
                <div>
                    <a href="add_driver.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        নতুন ড্রাইভার যোগ করুন
                    </a>
                </div>
            </div>

            <?php if (empty($drivers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-tie" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                    <h4 class="text-muted">কোনো ড্রাইভার যোগ করা হয়নি</h4>
                    <p class="text-muted">প্রথম ড্রাইভার যোগ করতে নিচের বাটনে ক্লিক করুন</p>
                    <a href="add_driver.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        প্রথম ড্রাইভার যোগ করুন
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>নাম</th>
                                <th>আইডি নম্বর</th>
                                <th>ফোন</th>
                                <th>লাইসেন্স</th>
                                <th>অভিজ্ঞতা</th>
                                <th>অবস্থা</th>
                                <th>যোগ করা হয়েছে</th>
                                <th>কার্যক্রম</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drivers as $driver): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle me-2" style="font-size: 1.5rem; color: #667eea;"></i>
                                            <div>
                                                <strong><?php echo htmlspecialchars($driver['driver_name']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php if ($driver['gender'] === 'male'): ?>
                                                        <i class="fas fa-mars text-primary"></i> পুরুষ
                                                    <?php else: ?>
                                                        <i class="fas fa-venus text-danger"></i> মহিলা
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($driver['driver_id_number']); ?></span>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone me-1"></i>
                                        <?php echo htmlspecialchars($driver['phone']); ?>
                                        <?php if ($driver['email']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($driver['email']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($driver['license_number']); ?></span>
                                        <?php if ($driver['license_expiry']): ?>
                                            <br>
                                            <small class="text-muted">
                                                মেয়াদ: <?php echo date('d-m-Y', strtotime($driver['license_expiry'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($driver['experience_years'] > 0): ?>
                                            <span class="badge bg-warning"><?php echo $driver['experience_years']; ?> বছর</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">নতুন</span>
                                        <?php endif; ?>
                                        <?php if ($driver['salary'] > 0): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-money-bill me-1"></i>
                                                <?php echo number_format($driver['salary']); ?> টাকা
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($driver['status'] === 'active'): ?>
                                            <span class="badge badge-success">সক্রিয়</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">নিষ্ক্রিয়</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d-m-Y', strtotime($driver['created_at'])); ?>
                                    </td>
                                    <td>
                                        <a href="edit_driver.php?id=<?php echo $driver['id']; ?>" class="btn btn-sm btn-outline-primary" title="সম্পাদনা">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" title="মুছে ফেলুন" onclick="deleteDriver(<?php echo $driver['id']; ?>, '<?php echo htmlspecialchars($driver['driver_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        মোট <?php echo count($drivers); ?>জন ড্রাইভার পাওয়া গেছে
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteDriver(driverId, driverName) {
            if (confirm('আপনি কি "' + driverName + '" ড্রাইভারকে মুছে ফেলতে চান?')) {
                // AJAX দিয়ে ডিলিট রিকোয়েস্ট পাঠান
                fetch('delete_driver.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'driver_id=' + driverId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('ড্রাইভার সফলভাবে মুছে ফেলা হয়েছে!');
                        location.reload();
                    } else {
                        alert('ড্রাইভার মুছতে সমস্যা হয়েছে: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('সমস্যা হয়েছে: ' + error);
                });
            }
        }
    </script>
</body>
</html>
