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

            <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

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
                                <th>শিক্ষার্থী</th>
                                <th>স্কুল</th>
                                <th>রুট</th>
                                <th>ওঠার স্থান</th>
                                <th>পিকআপের/<br>গন্তব্যের স্থান</th>
                                <th>অভিভাবক</th>
                                <th>ঠিকানা</th>
                                <th>অবস্থা</th>
                                <th>কার্যক্রম</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($students as $student):
                                $routeName = '';
                                $allrouteresults = [];
                                $subRouteName = '';
                                $allsubrouteresults = [];
                                if ($student['route_id'] > 0) {
                                    // রুটের নাম আনুন
                                    try {
                                        $stmt = $pdo->prepare("SELECT route_name FROM routes WHERE id = ?");
                                        $stmt->execute([$student['route_id']]);
                                        $route = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $routeName = $route ? $route['route_name'] : 'N/A';
                                        $stmt = null;
                                    } catch (PDOException $e) {
                                        $routeName = 'N/A';
                                    }
                                } else {
                                    // সাব-রুট ও পিকআপ লোকেশন অনুযায়ী তথ্য আনুন
                                    $sql = "SELECT * 
                                            FROM routes";
                                    try {
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute();
                                        $allrouteresults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        $stmt = null;
                                    } catch (PDOException $e) {
                                        $routeName = 'N/A';
                                    }
                                }

                                if ($student['sub_route_id'] > 0) {
                                    // সাব-রুটের নাম আনুন
                                    try {
                                        $stmt = $pdo->prepare("SELECT destination_name FROM route_sub_destinations WHERE id = ?");
                                        $stmt->execute([$student['sub_route_id']]);
                                        $subRoute = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $subRouteName = $subRoute ? $subRoute['destination_name'] : 'N/A';
                                        $stmt = null;
                                    } catch (PDOException $e) {
                                        $subRouteName = 'N/A';
                                    }
                                } else {
                                    // সব সাব-রুট আনুন
                                    $sql = "SELECT * 
                                            FROM route_sub_destinations where route_id = ".(int)$student['route_id'];
                                    try {
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute();
                                        $allsubrouteresults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        $stmt = null;
                                    } catch (PDOException $e) {
                                        $subRouteName = 'N/A';
                                    }
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
                                    <td style="white-space: nowrap;"><?php echo htmlspecialchars($student['student_name']); ?>
                                    <?php
                                        if ($student['gender'] === 'male') {
                                            echo '<span class="text-info ms-2"><i class="fa-solid fa-mars"></i></span>';
                                        } elseif ($student['gender'] === 'female') {
                                            echo '<span class="text-warning ms-2"><i class="fa-solid fa-venus"></i></span>';
                                        } else {
                                            echo '<span class="text-secondary ms-2"><i class="fa-solid fa-genderless"></i></span>';
                                        }
                                        ?>
                                     <br><small style="white-space: nowrap; font-size:small"><i class="fa-solid fa-phone me-2"></i><?php echo htmlspecialchars($student['phone']); ?></small></td>
                                    <td>
                                        <?= htmlspecialchars($student['school_name']); ?> <br>
                                        <span style="font-size: small;">Id: <?= htmlspecialchars($student['student_id']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                            if (!empty($routeName) && $student['route_id'] != 0) {
                                                // রুটের নাম প্রদর্শন করুন
                                                echo htmlspecialchars($routeName); 
                                            } else {
                                                echo '<select class="form-control" name="route" id="route" style="width: 120px;" onchange="addStudentRouteChange(this.value, '.$student['id'].')">';
                                                echo '<option value="">নির্বাচন করুন</option>';
                                                if (!empty($allrouteresults)) {
                                                    foreach ($allrouteresults as $routeOption) {
                                                        echo '<option value="' . htmlspecialchars($routeOption['id']) . '">' . htmlspecialchars($routeOption['route_name']) . '</option>';
                                                    }
                                                echo'</select>';
                                                }
                                                
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            if (!empty($subRouteName) && $student['sub_route_id'] != 0) {
                                                // সাব-রুটের নাম প্রদর্শন করুন
                                                echo htmlspecialchars($subRouteName); 
                                            } else {
                                                if ($routeName == '') {
                                                    echo '<select class="form-control" name="sub_route" id="sub_route" style="width: 150px;" disabled>';
                                                } else {
                                                echo '<select class="form-control" name="sub_route" id="sub_route" style="width: 150px;" onchange="addStudentSubRouteChange(this.value, '.$student['id'].')">';
                                                }
                                                echo '<option value="">নির্বাচন করুন</option>';
                                                if (!empty($allsubrouteresults)) {
                                                    foreach ($allsubrouteresults as $subRouteOption) {
                                                        echo '<option value="' . htmlspecialchars($subRouteOption['id']) . '">' . htmlspecialchars($subRouteOption['destination_name']) . '</option>';
                                                    }
                                                echo'</select>';
                                                }
                                            }
                                                
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student['pickup_location']); ?>/<br>
                                        <?php echo htmlspecialchars($student['drop_location']); ?>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <span style="font-size: 15px;"><i class="fa-solid fa-mars me-2 text-info"></i><?php echo htmlspecialchars($student['father_name']); ?> (<?= htmlspecialchars($student['father_occupation']) ?>)</span><br>
                                        <span style="font-size: 15px;"><i class="fa-solid fa-venus me-2 text-warning"></i><?php echo htmlspecialchars($student['mother_name']); ?> (<?= htmlspecialchars($student['mother_occupation']) ?>)</span></br>
                                        <span style="font-size: small;"><i class="fa-solid fa-phone me-2 text-primary "></i><?php echo htmlspecialchars($student['guardian_phone']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['address']); ?></td>
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

        function addStudentRouteChange(id, stdentId) {
            // এখানে আপনি রুট পরিবর্তনের জন্য প্রয়োজনীয় জাভাস্ক্রিপ্ট কোড যোগ করতে পারেন
            if(confirm('আপনি কি এই শিক্ষার্থীর রুট পরিবর্তন করতে চান?')) {
                window.location.href = 'set_student_route.php?student_id=' + stdentId + '&route_id=' + id;
            }
        }
        function addStudentSubRouteChange(id, stdentId) {
            // এখানে আপনি সাব-রুট পরিবর্তনের জন্য প্রয়োজনীয় জাভাস্ক্রিপ্ট কোড যোগ করতে পারেন
            if(confirm('আপনি কি এই শিক্ষার্থীর সাব-রুট পরিবর্তন করতে চান?')) {
                window.location.href = 'set_student_sub_route.php?student_id=' + stdentId + '&sub_route_id=' + id;
            }
        }
    </script>
</body>
</html>
