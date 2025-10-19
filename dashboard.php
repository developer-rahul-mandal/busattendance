<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন - যদি লগইন না করা থাকে তাহলে লগইন পেজে রিডাইরেক্ট করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// সেশন টাইমআউট চেক করুন (২৪ ঘন্টা)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// লগআউট প্রসেসিং - এখন logout.php ফাইলে পরিচালিত হয়
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড - বাস উপস্থিতি ব্যবস্থাপনা</title>
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
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .dashboard-header {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            padding: 30px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid #667eea;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stats-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .stats-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            color: white;
        }
        .quick-actions {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 25px;
        }
        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        /* মাস্টার মেনু স্টাইল */
        .master-menu {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 30px;
        }
        .master-menu h4 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        .master-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        .master-item:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .master-item h6 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .master-item p {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .master-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .master-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .master-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- নেভিগেশন বার -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-bus me-2"></i>
                বাস উপস্থিতি ব্যবস্থাপনা
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    স্বাগতম, <?php echo htmlspecialchars($_SESSION['super_admin_name']); ?>
                </span>
                <a href="logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    লগআউট
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- স্বাগতম কার্ড -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        সুপার অ্যাডমিন ড্যাশবোর্ড
                    </h2>
                    <p class="mb-0">
                        বাস উপস্থিতি ব্যবস্থাপনা সিস্টেমে স্বাগতম। 
                        এখান থেকে আপনি সমস্ত কার্যক্রম পরিচালনা করতে পারবেন।
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-user-shield" style="font-size: 4rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>

        <!-- পরিসংখ্যান কার্ড -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users stats-icon"></i>
                    <div class="stats-number">
                        <?php
                        // মোট শিক্ষার্থী সংখ্যা গণনা করুন
                        $stmt = $pdo->query("SELECT COUNT(*) AS total_students FROM students WHERE status = 'active'");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $result['total_students'];
                        ?>
                    </div>
                    <div class="stats-label">মোট শিক্ষার্থী</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-bus stats-icon"></i>
                    <div class="stats-number">
                        <?php
                        // সক্রিয় বাস সংখ্যা গণনা করুন
                        $stmt = $pdo->query("SELECT COUNT(*) AS total_buses FROM buses WHERE status = 'active'");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $result['total_buses'];
                        ?>
                    </div>
                    <div class="stats-label">সক্রিয় বাস</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-route stats-icon"></i>
                    <div class="stats-number">
                        <?php
                        // মোট রুট সংখ্যা গণনা করুন
                        $stmt = $pdo->query("SELECT COUNT(*) AS total_routes FROM routes WHERE status = 'active'");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $result['total_routes'];
                        ?>
                    </div>
                    <div class="stats-label">বাস রুট</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-check stats-icon"></i>
                    <div class="stats-number">0</div>
                    <div class="stats-label">আজকের উপস্থিতি</div>
                </div>
            </div>
        </div>

        <!-- মাস্টার মেনু -->
        <div class="row">
            <div class="col-12">
                <div class="master-menu">
                    <h4>
                        <i class="fas fa-cogs me-2"></i>
                        মাস্টার ম্যানেজমেন্ট
                    </h4>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="master-item">
                                <i class="fas fa-bus master-icon"></i>
                                <h6>বাস ব্যবস্থাপনা</h6>
                                <p>নতুন বাস যোগ করুন, বাসের তথ্য সম্পাদনা করুন এবং বাসের তালিকা দেখুন।</p>
                                <a href="add_bus.php" class="master-btn">
                                    <i class="fas fa-plus me-1"></i>
                                    বাস যোগ করুন
                                </a>
                                <a href="bus_list.php" class="master-btn" style="margin-left: 10px;">
                                    <i class="fas fa-list me-1"></i>
                                    বাস তালিকা
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="master-item">
                                <i class="fas fa-user-tie master-icon"></i>
                                <h6>ড্রাইভার ব্যবস্থাপনা</h6>
                                <p>নতুন ড্রাইভার যোগ করুন, ড্রাইভারের তথ্য সম্পাদনা করুন এবং ড্রাইভার তালিকা দেখুন।</p>
                                <a href="add_driver.php" class="master-btn">
                                    <i class="fas fa-plus me-1"></i>
                                    ড্রাইভার যোগ করুন
                                </a>
                                <a href="driver_list.php" class="master-btn" style="margin-left: 10px;">
                                    <i class="fas fa-list me-1"></i>
                                    ড্রাইভার তালিকা
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="master-item">
                                <i class="fas fa-route master-icon"></i>
                                <h6>রুট ব্যবস্থাপনা</h6>
                                <p>বাস রুট যোগ করুন, রুটের তথ্য সম্পাদনা করুন এবং রুটের তালিকা দেখুন।</p>
                                <a href="add_route.php" class="master-btn">
                                    <i class="fas fa-plus me-1"></i>
                                    রুট যোগ করুন
                                </a>
                                <a href="route_list.php" class="master-btn" style="margin-left: 10px;">
                                    <i class="fas fa-list me-1"></i>
                                    রুট তালিকা
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="master-item">
                                <i class="fas fa-users master-icon"></i>
                                <h6>শিক্ষার্থী ব্যবস্থাপনা</h6>
                                <p>নতুন শিক্ষার্থী যোগ করুন, শিক্ষার্থীর তথ্য সম্পাদনা করুন এবং শিক্ষার্থী তালিকা দেখুন।</p>
                                <a href="add_student.php" class="master-btn">
                                    <i class="fas fa-plus me-1"></i>
                                    শিক্ষার্থী যোগ করুন
                                </a>
                                <a href="student_list.php" class="master-btn" style="margin-left: 10px;">
                                    <i class="fas fa-list me-1"></i>
                                    শিক্ষার্থী তালিকা
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="master-item">
                                <i class="fa-solid fa-handshake master-icon"></i>
                                <h6>মহিলা পরিচারিকা ব্যবস্থাপনা</h6>
                                <p>নতুন মহিলা পরিচারিকা যোগ করুন, মহিলা পরিচারিকা তথ্য সম্পাদনা করুন এবং মহিলা পরিচারিকা তালিকা দেখুন।</p>
                                <a href="add_attendant.php" class="master-btn">
                                    <i class="fas fa-plus me-1"></i>
                                    মহিলা পরিচারিকা যোগ করুন
                                </a>
                                <a href="attendant_list.php" class="master-btn" style="margin-left: 10px;">
                                    <i class="fas fa-list me-1"></i>
                                    মহিলা পরিচারিকা তালিকা
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="master-item">
                                <i class="fa-solid fa-font-awesome master-icon"></i>
                                <h6>রুট পরিচারিকা বরাদ্দ করুন</h6>
                                <p>নতুন রুট পরিচারিকা যোগ করুন, রুট পরিচারিকার তথ্য সম্পাদনা করুন এবং রুট পরিচারিকার তালিকা দেখুন।</p>
                                <a href="add_route_attendant.php" class="master-btn">
                                    <i class="fas fa-plus me-1"></i>
                                    রুট পরিচারিকা যোগ করুন
                                </a>
                                <a href="route_attendant_list.php" class="master-btn" style="margin-left: 10px;">
                                    <i class="fas fa-list me-1"></i>
                                    রুট পরিচারিকা তালিকা
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- দ্রুত কার্যক্রম -->
        <div class="row">
            <div class="col-12">
                <div class="quick-actions">
                    <h4 class="mb-4">
                        <i class="fas fa-bolt me-2"></i>
                        দ্রুত কার্যক্রম
                    </h4>
                    <div class="text-center">
                        <a href="add_student.php" class="action-btn">
                            <i class="fas fa-plus me-2"></i>
                            নতুন শিক্ষার্থী যোগ করুন
                        </a>
                        <a href="add_bus.php" class="action-btn">
                            <i class="fas fa-bus me-2"></i>
                            নতুন বাস যোগ করুন
                        </a>
                        <a href="add_driver.php" class="action-btn">
                            <i class="fas fa-user-tie me-2"></i>
                            নতুন ড্রাইভার যোগ করুন
                        </a>
                        <a href="attendance_report.php" class="action-btn">
                            <i class="fas fa-chart-bar me-2"></i>
                            উপস্থিতি রিপোর্ট
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- সিস্টেম তথ্য -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-header">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        সিস্টেম তথ্য
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>লগইন সময়:</strong> <?php echo date('d-m-Y H:i:s', $_SESSION['login_time']); ?></p>
                            <p><strong>ব্যবহারকারী:</strong> <?php echo htmlspecialchars($_SESSION['super_admin_username']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>ইমেইল:</strong> <?php echo htmlspecialchars($_SESSION['super_admin_email']); ?></p>
                            <p><strong>সেশনের সময়:</strong> ২৪ ঘন্টা</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>