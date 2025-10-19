<?php
session_start();
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>শিক্ষার্থী যোগ করুন - বাস উপস্থিতি ব্যবস্থাপনা</title>
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
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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
        .student-icon {
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
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <div class="form-header">
                        <i class="fas fa-user-graduate student-icon"></i>
                        <h3>নতুন শিক্ষার্থী যোগ করুন</h3>
                        <p>শিক্ষার্থীর তথ্য প্রবেশ করে নতুন শিক্ষার্থী সিস্টেমে যোগ করুন</p>
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

                    <form action="process_student_public_registration.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_name" class="form-label">শিক্ষার্থীর নাম <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="student_name" name="student_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">শিক্ষার্থী ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="student_id" name="student_id" required>
                                    <div class="form-text">যেমন: ST-001, ST-002</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label for="image" class="form-label">ছবি যোগ করুন <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
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
                                                echo '<option value="' . htmlspecialchars($route['id']) . '">' . htmlspecialchars($route['route_name']) .'('. htmlspecialchars($route['route_code']). ')</option>';
                                            }
                                        } catch (PDOException $e) {}
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
                                    <label for="phone" class="form-label">ফোন নম্বর <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guardian_phone" class="form-label">অভিভাবকের ফোন</label>
                                    <input type="tel" class="form-control" id="guardian_phone" name="guardian_phone">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="father_name" class="form-label">পিতার নাম</label>
                                    <input type="text" class="form-control" id="father_name" name="father_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mother_name" class="form-label">মাতার নাম</label>
                                    <input type="text" class="form-control" id="mother_name" name="mother_name">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">ঠিকানা <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="শিক্ষার্থীর পূর্ণ ঠিকানা..." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">লিঙ্গ</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="male" value="male" checked>
                                            <label class="form-check-label" for="male">
                                                পুরুষ
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="female" value="female">
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
                                            <input class="form-check-input" type="radio" name="status" id="active" value="active" checked>
                                            <label class="form-check-label" for="active">
                                                সক্রিয়
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive">
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
                                শিক্ষার্থী যোগ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('route').addEventListener('change', function() {
            const routeId = this.value;
            const subRouteSelect = document.getElementById('sub_route');
            subRouteSelect.innerHTML = '<option value="">লোড হচ্ছে...</option>';
            subRouteSelect.disabled = true;

            if (routeId) {
                fetch('apis/fetch-sub-route.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ route_id: routeId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        let options = '<option value="">নির্বাচন করুন</option>';
                        data.data.forEach(subRoute => {
                            options += `<option value="${subRoute.id}">${subRoute.destination_name}</option>`;
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
        });
    </script>
</body>
</html>
