<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// ড্রাইভার ID চেক করুন
$student_id = (int)($_GET['id'] ?? 0);
if ($student_id <= 0) {
    header('Location: student_list.php');
    exit();
}

// ড্রাইভার তথ্য আনুন
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
    $stmt->execute([':id' => $student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        $_SESSION['error_message'] = "শিক্ষার্থী পাওয়া যায়নি";
        header('Location: student_list.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে";
    header('Location: student_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>শিক্ষার্থীর সম্পাদনা - বাস উপস্থিতি ব্যবস্থাপনা</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-top: 30px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }

        .form-header h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            margin: 0;
        }

        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .driver-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <div class="form-header">
                        <i class="fas fa-edit driver-icon"></i>
                        <h3>শিক্ষার্থী সম্পাদনা করুন</h3>
                        <p>শিক্ষার্থীর তথ্য আপডেট করুন</p>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="process_edit_student.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_name" class="form-label">শিক্ষার্থীর নাম <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student['student_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">শিক্ষার্থী ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">ছবি আপডেট করুন</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <?php if (!empty($student['img_path'])): ?>
                                        <small class="form-text text-muted">বর্তমান ছবি:
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" class="btn btn-outline-warning btn-sm ms-2 mt-2">
                                                VIEW
                                            </a>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="school_name" class="form-label">স্কুলের নাম</label>
                                    <input type="text" class="form-control" id="school_name" name="school_name" value="<?php echo htmlspecialchars($student['school_name']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="route" class="form-label">রুট <span class="text-danger">*</span></label>
                                    <select class="form-control" id="route" name="route" required>
                                        <option value="">নির্বাচন করুন</option>
                                        <?php
                                        // রুট ডেটা ফেচ করুন
                                        try {
                                            $stmt = $pdo->query("SELECT id, route_name, route_code FROM routes");
                                            $routes = $stmt->fetchAll();
                                            foreach ($routes as $route) {
                                                if ($route['id'] == $student['route_id']) {
                                                    echo '<option value="' . htmlspecialchars($route['id']) . '" selected>' . htmlspecialchars($route['route_name']) . '(' . htmlspecialchars($route['route_code']) . ')</option>';
                                                    continue;
                                                }
                                                echo '<option value="' . htmlspecialchars($route['id']) . '">' . htmlspecialchars($route['route_name']) . '(' . htmlspecialchars($route['route_code']) . ')</option>';
                                            }
                                        } catch (PDOException $e) {
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sub_route" class="form-label">ওঠার স্থান <span class="text-danger">*</span></label>
                                    <select class="form-control" id="sub_route" name="sub_route" required disabled>
                                        <option value="">নির্বাচন করুন</option>
                                        <!-- সেকশন ডেটা জাভাস্ক্রিপ্ট দিয়ে লোড হবে -->


                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pickup_location" class="form-label">পিকআপের স্থান<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="pickup_location" name="pickup_location" value="<?= htmlspecialchars($student['pickup_location']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="drop_location" class="form-label">গন্তব্যের স্থান</label>
                                    <input type="text" class="form-control" id="drop_location" name="drop_location" value="<?= htmlspecialchars($student['drop_location']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">পিতার ফোন নম্বর <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guardian_phone" class="form-label">মাতার ফোন নম্বর </label>
                                    <input type="tel" class="form-control" id="guardian_phone" name="guardian_phone" value="<?php echo htmlspecialchars($student['guardian_phone']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="father_name" class="form-label">পিতার নাম</label>
                                    <input type="text" class="form-control" id="father_name" name="father_name" value="<?php echo htmlspecialchars($student['father_name']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mother_name" class="form-label">মাতার নাম</label>
                                    <input type="text" class="form-control" id="mother_name" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="father_occupation" class="form-label">পিতার পেশা</label>
                                    <input type="text" class="form-control" id="father_occupation" name="father_occupation" value="<?= htmlspecialchars($student['father_occupation']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mother_occupation" class="form-label">মাতার পেশা</label>
                                    <input type="text" class="form-control" id="mother_occupation" name="mother_occupation" value="<?= htmlspecialchars($student['mother_occupation']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">ঠিকানা <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="শিক্ষার্থীর পূর্ণ ঠিকানা..." required><?php echo htmlspecialchars($student['address']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">লিঙ্গ</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="male" value="male" <?php if ($student['gender'] === 'male') echo 'checked'; ?>>
                                            <label class="form-check-label" for="male">
                                                পুরুষ
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="female" value="female" <?php if ($student['gender'] === 'female') echo 'checked'; ?>>
                                            <label class="form-check-label" for="female">
                                                মহিলা
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">অবস্থা</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="active" value="active" <?php if ($student['status'] === 'active') echo 'checked'; ?>>
                                            <label class="form-check-label" for="active">
                                                সক্রিয়
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive" <?php if ($student['status'] === 'inactive') echo 'checked'; ?>>
                                            <label class="form-check-label" for="inactive">
                                                নিষ্ক্রিয়
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary me-3">
                                <i class="fas fa-save me-2"></i>
                                আপডেট করুন
                            </button>
                            <a href="student_list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                তালিকায় ফিরুন
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--model -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">বর্তমান ছবি</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="./uploads/<?= htmlspecialchars($student['img_path']); ?>" alt="Current Image" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        addEventListener('DOMContentLoaded', () => {
            // পেজ লোড হলে সাব-রুট লোড করুন
            document.getElementById('route').dispatchEvent(new Event('change'));
        });

        function handleInputChange() {
            const routeId = this.value;
            const studentSubRouteId = <?php echo (int)$student['sub_route_id']; ?>;
            const subRouteSelect = document.getElementById('sub_route');
            subRouteSelect.innerHTML = '<option value="">লোড হচ্ছে...</option>';
            subRouteSelect.disabled = true;

            if (routeId) {
                fetch('apis/fetch-sub-route.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            route_id: routeId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.data.length > 0) {
                            let options = '<option value="">নির্বাচন করুন</option>';
                            data.data.forEach(subRoute => {
                                if (subRoute.id == studentSubRouteId) {
                                    options += `<option value="${subRoute.id}" selected>${subRoute.destination_name}</option>`;
                                } else {
                                    options += `<option value="${subRoute.id}">${subRoute.destination_name}</option>`;
                                }
                            });
                            subRouteSelect.innerHTML = options;
                            subRouteSelect.disabled = false;
                        } else {
                            subRouteSelect.innerHTML = '<option value="">কোনো সাব-রুট পাওয়া যায়নি</option>';
                            subRouteSelect.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('ত্রুটি:', error);
                        subRouteSelect.innerHTML = '<option value="">লোড করতে সমস্যা হয়েছে</option>';
                        subRouteSelect.disabled = true;
                    });
            } else {
                subRouteSelect.innerHTML = '<option value="">নির্বাচন করুন</option>';
                subRouteSelect.disabled = true;
            }
        }

        document.getElementById('route').addEventListener('change', handleInputChange);
    </script>
</body>

</html>