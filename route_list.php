<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// রুট তালিকা আনুন
try {
    $sql = "
    SELECT r.*, 
           GROUP_CONCAT(rsd.destination_name ORDER BY rsd.sequence_order SEPARATOR ' → ') as sub_destinations
    FROM routes r 
    LEFT JOIN route_sub_destinations rsd ON r.id = rsd.route_id 
    GROUP BY r.id 
    ORDER BY r.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $routes = $stmt->fetchAll();
} catch (PDOException $e) {
    $routes = [];
    $error_message = "রুট তালিকা লোড করতে সমস্যা হয়েছে";
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রুট তালিকা - বাস উপস্থিতি ব্যবস্থাপনা</title>
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
        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 30px;
            margin-top: 30px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .page-header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .page-header p {
            color: #666;
            margin: 0;
        }
        .route-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table thead th {
            border: none;
            font-weight: 600;
            padding: 15px;
        }
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        .badge {
            font-size: 0.85rem;
            padding: 8px 12px;
            border-radius: 20px;
        }
        .badge-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        .badge-danger {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 6px;
            margin: 0 2px;
        }
        .btn-outline-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
        }
        .btn-outline-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.3);
        }
        .route-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .route-route {
            font-weight: 600;
            color: #667eea;
        }
        .sub-destinations {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
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
        <div class="main-container">
            <div class="page-header">
                <i class="fas fa-route route-icon"></i>
                <h2>রুট তালিকা</h2>
                <p>সমস্ত রুটের তালিকা এবং ব্যবস্থাপনা</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-0">মোট রুট: <span class="badge bg-primary"><?php echo count($routes); ?></span></h5>
                </div>
                <div>
                    <a href="add_route.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        নতুন রুট যোগ করুন
                    </a>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($routes)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-route fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">কোনো রুট পাওয়া যায়নি</h5>
                    <p class="text-muted">নতুন রুট যোগ করতে নিচের বাটনে ক্লিক করুন</p>
                    <a href="add_route.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        প্রথম রুট যোগ করুন
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>রুটের নাম</th>
                                <th>রুট কোড</th>
                                <th>পথ</th>
                                <th>দূরত্ব</th>
                                <th>সময়</th>
                                <th>অবস্থা</th>
                                <th>যোগের তারিখ</th>
                                <th>কার্যক্রম</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($routes as $route): ?>
                                <tr>
                                    <td>
                                        <div class="route-info">
                                            <div class="fw-bold"><?php echo htmlspecialchars($route['route_name']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($route['route_code']); ?></span>
                                    </td>
                                    <td>
                                        <div class="route-route">
                                            <?php echo htmlspecialchars($route['start_location']); ?> → <?php echo htmlspecialchars($route['end_location']); ?>
                                        </div>
                                        <?php if (!empty($route['sub_destinations'])): ?>
                                            <div class="sub-destinations">
                                                <small><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($route['sub_destinations']); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($route['distance'] > 0): ?>
                                            <?php echo number_format($route['distance'], 1); ?> কিমি
                                        <?php else: ?>
                                            <span class="text-muted">নির্ধারিত নয়</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($route['estimated_time'] > 0): ?>
                                            <?php echo $route['estimated_time']; ?> মিনিট
                                        <?php else: ?>
                                            <span class="text-muted">নির্ধারিত নয়</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($route['status'] === 'active'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle me-1"></i>সক্রিয়
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times-circle me-1"></i>নিষ্ক্রিয়
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d-m-Y', strtotime($route['created_at'])); ?>
                                    </td>
                                    <td>
                                        <a href="edit_route.php?id=<?php echo $route['id']; ?>" class="btn btn-sm btn-outline-primary" title="সম্পাদনা">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" title="মুছে ফেলুন" onclick="deleteRoute(<?php echo $route['id']; ?>, '<?php echo htmlspecialchars($route['route_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteRoute(routeId, routeName) {
            if (confirm('আপনি কি "' + routeName + '" রুটটি মুছে ফেলতে চান?')) {
                // AJAX দিয়ে ডিলিট রিকোয়েস্ট পাঠান
                fetch('delete_route.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'route_id=' + routeId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('রুট সফলভাবে মুছে ফেলা হয়েছে!');
                        location.reload();
                    } else {
                        alert('রুট মুছতে সমস্যা হয়েছে: ' + data.message);
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
