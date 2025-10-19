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

    <div class="container">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3><i class="fas fa-user-graduate me-2"></i>শিক্ষার্থীর তালিকা</h3>
                    <p class="text-muted">সকল শিক্ষার্থীর তালিকা এবং তথ্য</p>
                </div>
                <div>
                    <a href="add_student.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        নতুন শিক্ষার্থী যোগ করুন
                    </a>
                </div>
            </div>

            <?php if (empty($students)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-tie" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                    <h4 class="text-muted">কোনো শিক্ষার্থী যোগ করা হয়নি</h4>
                    <p class="text-muted">প্রথম শিক্ষার্থী যোগ করতে নিচের বাটনে ক্লিক করুন</p>
                    <a href="add_student.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        প্রথম শিক্ষার্থী যোগ করুন
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ছবি</th>
                                <th>নাম</th>
                                <th>শিক্ষার্থী ID</th>
                                <th>রুট</th>
                                <th>ওঠার স্থান</th>
                                <th>ফোন নম্বর</th>
                                <th>অভিভাবকের ফোন</th>
                                <th>পিতার নাম</th>
                                <th>মাতার নাম</th>
                                <th>ঠিকানা</th>
                                <th>লিঙ্গ</th>
                                <th>অবস্থা</th>
                                <th>কার্যক্রম</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($students as $student):
                                // রুটের নাম আনুন
                                try {
                                    $stmt = $pdo->prepare("SELECT route_name FROM routes WHERE id = ?");
                                    $stmt->execute([$student['route_id']]);
                                    $route = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $routeName = $route ? $route['route_name'] : 'N/A';
                                    $stmt = null; // স্টেটমেন্ট ক্লিয়ার করুন
                                } catch (PDOException $e) {
                                    $routeName = 'N/A';
                                }
                                try {
                                    $stmt = $pdo->prepare("SELECT destination_name FROM route_sub_destinations WHERE id = ?");
                                    $stmt->execute([$student['sub_route_id']]);
                                    $subRoute = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $subRouteName = $subRoute ? $subRoute['destination_name'] : 'N/A';
                                } catch (PDOException $th) {
                                    $subRouteName = "N/A";
                                }
                            ?>
                                <tr>
                                    <td>
                                        <?php if ($student['img_path']): ?>
                                            <img src="<?php echo './uploads/'.htmlspecialchars($student['img_path']); ?>" alt="ছবি" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/50?text=No+Image" alt="No Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($routeName); ?></td>
                                    <td><?php echo htmlspecialchars($subRouteName); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($student['guardian_phone']); ?></td>
                                    <td><?php echo htmlspecialchars($student['father_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['mother_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['address']); ?></td>
                                    <td>
                                        <?php
                                        if ($student['gender'] === 'male') {
                                            echo '<span class="badge bg-info">পুরুষ</span>';
                                        } elseif ($student['gender'] === 'female') {
                                            echo '<span class="badge bg-warning text-dark">মহিলা</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">অন্যান্য</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($student['status'] === 'active') {
                                            echo '<span class="badge bg-success">সক্রিয়</span>';
                                        } else {
                                            echo '<span class="badge bg-danger">নিষ্ক্রিয়</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-primary" title="সম্পাদনা">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" title="মুছে ফেলুন" onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['student_id']); ?>')">
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
                        মোট <?php echo count($students); ?>জন শিক্ষার্থী পাওয়া গেছে
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteStudent(id, studentID) {
            if (confirm('আপনি কি "' + studentID + '" শিক্ষার্থীটি মুছে ফেলতে চান?')) {
                // AJAX দিয়ে ডিলিট রিকোয়েস্ট পাঠান
                fetch('delete_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('শিক্ষার্থী সফলভাবে মুছে ফেলা হয়েছে!');
                        location.reload();
                    } else {
                        alert('শিক্ষার্থী মুছতে সমস্যা হয়েছে: ' + data.message);
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
