<?php
session_start();
require_once '../config/database.php';

// সেশন চেক করুন - যদি লগইন না করা থাকে তাহলে লগইন পেজে রিডাইরেক্ট করুন
if (!isset($_SESSION['route_attendant_id']) || $_SESSION['attendant_logged_in'] !== true || !isset($_SESSION['date']) || $_SESSION['date'] !== date('Y-m-d')) {
    header('Location: login.php');
    exit();
}

// সেশন টাইমআউট চেক করুন (২৪ ঘন্টা)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("
SELECT 
    ra.id,
    ra.way,
    a.attendant_name,
    a.attendant_id_number,
    b.bus_number,
    b.bus_name,
    b.bus_type,
    b.capacity AS bus_capacity,
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
WHERE DATE(ra.created_at) = CURDATE() AND ra.id = :id LIMIT 1
");
$stmt->execute(['id' => $_SESSION['route_attendant_id']]);
$attendance_info = $stmt->fetch(PDO::FETCH_ASSOC);
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
                    স্বাগতম, <?php echo htmlspecialchars($attendance_info['attendant_name']); ?>
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
                        বাস অ্যাডমিন ড্যাশবোর্ড
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

        <!-- দ্রুত কার্যক্রম -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-actions">
                    <h4 class="mb-4">
                        <i class="fas fa-bolt me-2"></i>
                        দ্রুত কার্যক্রম
                    </h4>
                    <div class="text-center">
                        <a href="add_student.php" class="action-btn">
                            <i class="fa-solid fa-right-long me-2"></i>
                            শিক্ষার্থী বাসে তুলুন
                        </a>
                        <a href="add_bus.php" class="action-btn">
                            <i class="fa-solid fa-left-long me-2"></i>
                            শিক্ষার্থী বাসথেকে নামান
                        </a>
                        <!-- <a href="add_driver.php" class="action-btn">
                            <i class="fas fa-user-tie me-2"></i>
                            নতুন ড্রাইভার যোগ করুন
                        </a>
                        <a href="attendance_report.php" class="action-btn">
                            <i class="fas fa-chart-bar me-2"></i>
                            উপস্থিতি রিপোর্ট
                        </a> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- পরিসংখ্যান কার্ড -->
        <div class="row">
            <div class="col-md-6">
                <div class="stats-card text-center">
                    <i class="fas fa-bus stats-icon"></i>
                    <div class="stats-number"><?= htmlspecialchars($attendance_info['bus_number'])?></div>
                    <div class="stats-label">বাস</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card text-center">
                    <i class="fas fa-route stats-icon"></i>
                    <div class="stats-number"><?= htmlspecialchars($attendance_info['route_name']); ?> </div>
                    <div class="stats-label">বাস রুট</div>
                </div>
            </div>
        </div>
        <div class="row justify-content-between">
            <div class="col-md-6">
                <div class="stats-card text-center">
                    <i class="fas fa-users stats-icon"></i>
                    <div class="stats-number"><?= htmlspecialchars($attendance_info['bus_capacity']); ?></div>
                    <div class="stats-label">ধারণক্ষমতা</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-check stats-icon"></i>
                    <div class="stats-number">0</div>
                    <div class="stats-label">আজকের উপস্থিতি</div>
                </div>
            </div>
        </div>

        <!-- মাস্টার মেনু -->
        <!-- <div class="row">
            <div class="col-12">
                <div class="master-menu">
                    <h4>
                        <i class="fas fa-cogs me-2"></i>
                        বাস ম্যানেজমেন্ট
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
                    </div>
                </div>
            </div>
        </div> -->

        

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
                            <p><strong>ব্যবহারকারী:</strong> <?php echo htmlspecialchars($attendance_info['attendant_id_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>বাস:</strong> <?php echo htmlspecialchars($attendance_info['bus_number']); if(empty($attendance_info['bus_name'])) echo '(' . $attendance_info['bus_name'] . ')'  ?></p>
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