<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// বাস তালিকা আনুন
try {
    $sql = "
        SELECT 
            ra.id,
            ra.way,

            a.attendant_name,
            a.attendant_id_number,

            b.bus_number,
            b.bus_name,
            b.bus_type,

            r.route_name,
            r.route_code,

            d.driver_name,
            d.license_number,

            ra.status,
            ra.created_at,
            ra.updated_at

        FROM route_attendant ra
        LEFT JOIN attendants a ON ra.attendant = a.id
        LEFT JOIN buses b ON ra.bus = b.id
        LEFT JOIN routes r ON ra.route = r.id
        LEFT JOIN drivers d ON ra.driver = d.id
        WHERE DATE(ra.created_at) = CURDATE()
        ORDER BY ra.created_at DESC
    ";

    $stmt = $pdo->query($sql);
    $route_attendants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $route_attendants = [];
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রুট পরিচারিকা তালিকা - বাস উপস্থিতি ব্যবস্থাপনা</title>
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
                    <h3><i class="fas fa-bus me-2"></i>রুট পরিচারিকা তালিকা</h3>
                    <p class="text-muted">সকল রুট পরিচারিকার তালিকা এবং তথ্য</p>
                </div>
                <div>
                    <a href="add_route_attendant.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        নতুন রুট পরিচারিকা যোগ করুন
                    </a>
                </div>
            </div>

            <?php if (empty($route_attendants)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bus" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                    <h4 class="text-muted">কোনো রুট পরিচারিকা যোগ করা হয়নি</h4>
                    <p class="text-muted">প্রথম রুট পরিচারিকা যোগ করতে নিচের বাটনে ক্লিক করুন</p>
                    <a href="add_route_attendant.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        প্রথম রুট পরিচারিকা যোগ করুন
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>পথ</th>
                                <th>মহিলা পরিচারিকা</th>
                                <th>বাস</th>
                                <th>রুট</th>
                                <th>ড্রাইভার</th>
                                <th>অবস্থা</th>
                                <th>কার্যক্রম</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($route_attendants as $route_attendant): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php 
                                            if ($route_attendant['way'] === 'to_go') {
                                                echo '<span class="badge badge-success">যাওয়ার পথে</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">ফিরে আসার পথে</span>';
                                            }
                                            ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                        echo htmlspecialchars($route_attendant['attendant_name'] ?? 'N/A'); 
                                        echo '<br><small class="text-muted">ID: ' . htmlspecialchars($route_attendant['attendant_id_number'] ?? 'N/A') . '</small>';
                                        ?> 
                                    </td>
                                    <td>
                                        <?php 
                                        echo htmlspecialchars($route_attendant['bus_number'] ?? 'N/A'); 
                                        echo '<br><small class="text-muted">' . htmlspecialchars($route_attendant['bus_name'] ?? 'N/A') . ' (' . htmlspecialchars($route_attendant['bus_type'] ?? 'N/A') . ')</small>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        echo htmlspecialchars($route_attendant['route_name'] ?? 'N/A'); 
                                        echo '<br><small class="text-muted">Code: ' . htmlspecialchars($route_attendant['route_code'] ?? 'N/A') . '</small>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        echo htmlspecialchars($route_attendant['driver_name'] ?? 'N/A'); 
                                        echo '<br><small class="text-muted">License: ' . htmlspecialchars($route_attendant['license_number'] ?? 'N/A') . '</small>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($route_attendant['status'] === 'active') {
                                            echo '<span class="badge badge-success">সক্রিয়</span>';
                                        } else {
                                            echo '<span class="badge badge-danger">নিষ্ক্রিয়</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit_route_attendant.php?id=<?php echo $route_attendant['id']; ?>" class="btn btn-sm btn-warning me-2" title="সম্পাদনা করুন">
                                            <i class="fas fa-edit me-1"></i> 
                                        </a>
                                        <button onclick="deleteRouteAttendant(<?php echo $route_attendant['id']; ?>)" class="btn btn-sm btn-danger" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-alt me-1"></i>
                                        </button>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        মোট <?php echo count($route_attendants); ?>টি রুট পরিচারিকা পাওয়া গেছে
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteRouteAttendant(id) {
            if (confirm('আপনি কি রেকর্ড মুছে ফেলতে চান?')) {
                // AJAX দিয়ে ডিলিট রিকোয়েস্ট পাঠান
                fetch('delete_route_attendant.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('রেকর্ড সফলভাবে মুছে ফেলা হয়েছে!');
                        location.reload();
                    } else {
                        alert('রেকর্ড মুছতে সমস্যা হয়েছে: ' + data.message);
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
