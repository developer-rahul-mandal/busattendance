<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রুট যোগ করুন - বাস উপস্থিতি ব্যবস্থাপনা</title>
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
        .route-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .sub-destination {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .destination-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .destination-item:last-child {
            margin-bottom: 0;
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
            <div class="col-md-10">
                <div class="form-container">
                    <div class="form-header">
                        <i class="fas fa-route route-icon"></i>
                        <h3>নতুন রুট যোগ করুন</h3>
                        <p>রুটের তথ্য প্রবেশ করে নতুন রুট সিস্টেমে যোগ করুন</p>
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

                    <form action="process_add_route.php" method="POST" id="routeForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="route_name" class="form-label">রুটের নাম <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="route_name" name="route_name" required>
                                    <div class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="route_code" class="form-label">রুট কোড <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="route_code" name="route_code" required>
                                    <div class="form-text">যেমন: RT-001, RT-002</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_location" class="form-label">শুরু গন্তব্য <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="start_location" name="start_location" required>
                                    <div class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_location" class="form-label">শেষ গন্তব্য <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="end_location" name="end_location" required>
                                    <div class="form-text"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">উপ-গন্তব্য</label>
                            <div id="subDestinations">
                                <div class="sub-destination">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="sub_destinations[]" placeholder="উপ-গন্তব্যের নাম" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="sub_distances[]" placeholder="দূরত্ব (কিমি)" min="0" step="0.1">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control" name="sub_times[]" placeholder="সময় (মিনিট)" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeSubDestination(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSubDestination()">
                                <i class="fas fa-plus me-1"></i>
                                আরও উপ-গন্তব্য যোগ করুন
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="distance" class="form-label">দূরত্ব (কিলোমিটার)</label>
                                    <input type="number" class="form-control" id="distance" name="distance" min="0" step="0.1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estimated_time" class="form-label">আনুমানিক সময় (মিনিট)</label>
                                    <input type="number" class="form-control" id="estimated_time" name="estimated_time" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">বিবরণ</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="রুট সম্পর্কে অতিরিক্ত তথ্য..."></textarea>
                        </div>

                        <div class="row">
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
                                রুট যোগ করুন
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                ড্যাশবোর্ডে ফিরুন
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addSubDestination() {
            const container = document.getElementById('subDestinations');
            const newDestination = document.createElement('div');
            newDestination.className = 'sub-destination';
            newDestination.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="sub_destinations[]" placeholder="উপ-গন্তব্যের নাম" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="sub_distances[]" placeholder="দূরত্ব (কিমি)" min="0" step="0.1">
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="sub_times[]" placeholder="সময় (মিনিট)" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeSubDestination(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newDestination);
        }

        function removeSubDestination(button) {
            button.closest('.sub-destination').remove();
        }
    </script>
</body>
</html>
