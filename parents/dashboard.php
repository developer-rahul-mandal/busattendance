<?php
session_start();
require_once '../config/database.php';

// সেশন চেক করুন - যদি লগইন না করা থাকে তাহলে লগইন পেজে রিডাইরেক্ট করুন
if (!isset($_SESSION['student_id']) || $_SESSION['parent_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// সেশন টাইমআউট চেক করুন (২৪ ঘন্টা)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
    session_destroy();
    header('Location: login.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE `student_id` = :student_id LIMIT 1");
    $stmt->execute(['student_id' => $_SESSION['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['login_error'] = "Server error! Please try again.";
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
    <title>DASHBOARD</title>
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

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .dashboard-header {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            padding: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid #667eea;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        .quick-actions {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
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
                Bus Attendance System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    WELCOME, <?php echo htmlspecialchars($_SESSION['student_id']); ?>
                </span>
                <a href="logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    LOGOUT
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

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

        <!-- স্বাগতম কার্ড -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        STUDENT DASHBOARD
                    </h2>
                    <p class="mb-0">
                        STUDENT NAME: <?php echo strtoupper(htmlspecialchars($_SESSION['student_name'])); ?> | SCHOOL: <?php echo strtoupper(htmlspecialchars($_SESSION['school_name'])); ?>

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
                        FAST ACTIONS
                    </h4>
                    <div class="text-center">
                        <form id="idCardForm" style="display:inline-block;">
                            <input type="hidden" id="student_id" name="student_id" value="<?= $student['student_id']; ?>" required>
                            <input type="hidden" id="name" name="name" value="<?= $student['student_name']; ?>" required>
                            <input type="hidden" id="phone" name="phone" value="+91 <?= $student['phone']; ?>" required placeholder="Enter phone number">
                            <input type="hidden" id="school" name="school" value="<?= $student['school_name']; ?>" required placeholder="Enter school name">
                            <input type="hidden" id="student_image" name="student_image" value="../uploads/<?= $student['img_path']; ?>">
                            <input type="hidden" id="pickup" name="pickup" value="<?= $student['pickup_location']; ?>">
                            <input type="hidden" id="drop" name="drop" value="<?= $student['drop_location']; ?>">
                            <button type="submit" class="action-btn" id="downloadBtn">
                                <i class="fas fa-download me-2"></i> DOWNLOAD STUDENT ID CARD
                            </button>
                            <button type="submit" class="action-btn" id="printBtn">
                                <i class="fas fa-print me-2"></i> PRINT STUDENT ID CARD
                            </button>
                        </form>

                        <a href="#invoices">
                            <button class="action-btn" style="background: #ff3705ff; color: #fff;">
                                <i class="fas fa-file-invoice-dollar me-2"></i> VIEW INVOICES
                            </button>
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance History -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-actions">
                    <div style="width: 100% !important; position:relative;" class="mb-4">
                        <h4 class="mb-0 d-inline">
                            <i class="fas fa-history me-2"></i>
                            ATTENDANCE HISTORY
                        </h4>
                        <small class="ms-auto fs-6" style="position:absolute; right:1rem; font-family:Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif">LAST 7 DAYS</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Direction</th>
                                    <th scope="col">Bus</th>
                                    <th scope="col">Attendant</th>
                                    <th scope="col">Pickup Time</th>
                                    <th scope="col">Drop Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $stmt = $pdo->prepare("
                                    SELECT 
                                        DATE(pad.created_at) AS date,
                                        TIME(pad.pickup_time) AS pickup_time,
                                        TIME(pad.drop_time) AS drop_time,

                                        ra.way,

                                        att.attendant_name AS attendant_name,
                                        att.phone AS attendant_phone,

                                        b.bus_number AS bus_number,

                                        d.driver_name AS driver_name

                                    FROM pickup_and_drop AS pad
                                    JOIN students AS s ON pad.student_id = s.id
                                    JOIN route_attendant AS ra ON pad.route_attendant_id = ra.id
                                    LEFT JOIN attendants AS att ON ra.attendant = att.id
                                    LEFT JOIN buses AS b ON ra.bus = b.id
                                    LEFT JOIN drivers AS d ON ra.driver = d.id

                                    WHERE pad.student_id = :student_id
                                    AND DATE(pad.created_at) >= CURDATE() - INTERVAL 7 DAY

                                    ORDER BY pad.created_at DESC;
                                    ");
                                    $stmt->execute(['student_id' => $student['id']]);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['date']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['way'] == 'to_go' ? 'TO GO' : 'COME BACK') . '</td>';
                                        echo '<td>' . htmlspecialchars($row['bus_number']) . '<br><small>Drv.:' . htmlspecialchars($row['driver_name']) . '</small></td>';
                                        echo '<td>' . htmlspecialchars($row['attendant_name']) . '<br><small>Mob.:' . htmlspecialchars($row['attendant_phone']) . '</small></td>';
                                        echo '<td>' . htmlspecialchars($row['pickup_time']) . '</td>';
                                        echo '<td>' . ($row['drop_time'] ? htmlspecialchars($row['drop_time']) : '<span class="text-danger">Not Dropped Yet</span>') . '</td>';
                                        echo '</tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="4" class="text-center">Error fetching attendance history.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- invoices -->
        <div class="row mb-4" id="invoices">
            <div class="col-12">
                <div class="quick-actions">
                    <h4 class="mb-4">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        INVOICES
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Invoice ID</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Amount (INR)</th>
                                    <th scope="col">Payment Status</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE student_id = :student_id ORDER BY created_at DESC");
                                    $stmt->execute(['student_id' => $student['student_id']]);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<tr>';
                                        echo '<td># ' . htmlspecialchars($row['id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['invoice_date']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['amount']) . '</td>';
                                        echo '<td>' . ($row['payment_status'] == 'paid' ? '<span class="text-success">Paid</span>' : '<span class="text-danger">Unpaid</span>') . '</td>';
                                        echo '<td>' . htmlspecialchars(ucfirst($row['status'])) . '</td>';
                                        echo '<td><a href="view_invoice.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-sm btn-primary">View</a></td>';
                                        echo '</tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="5" class="text-center">Error fetching invoices.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./script.js"></script>
</body>

</html>