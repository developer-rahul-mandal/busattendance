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
    r.id as route_id,
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
    <style>
    /* ---------------------- */
    /* 1. Global/Container Styling */
    /* ---------------------- */
    .bus-stops-list {
        /* Adds a subtle top margin for separation from surrounding content */
        margin-top: 20px; 
        /* Max width helps keep content readable on very wide screens */
        max-width: 600px; 
        /* Centers the list if a max-width is set */
        margin-left: auto;
        margin-right: auto;
        padding: 0; /* Remove default padding */
        list-style: none; /* Ensure no bullet points if you change the tag to <ul> */
    }

    /* ---------------------- */
    /* 2. Individual Stop Item Styling (The Card) */
    /* ---------------------- */
    .bus-stop-item {
        display: flex; /* Use Flexbox for easy alignment */
        align-items: center; /* Vertically centers content */
        justify-content: space-between; /* Pushes label/name to opposite ends if needed */
        
        /* Spacing and visual depth */
        padding: 15px 20px;
        margin-bottom: 12px;
        
        /* Card appearance */
        background-color: #ffffff; /* White background */
        border-radius: 8px; /* Rounded corners */
        border: 1px solid #e0e0e0; /* Light border for definition */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); /* Subtle lift effect */
        
        /* Transition for hover effect */
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    /* Interactive hover effect */
    .bus-stop-item:hover {
        transform: translateY(-2px); /* Lifts the card slightly */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Darker shadow on hover */
    }

    /* ---------------------- */
    /* 3. Text/Content Styling */
    /* ---------------------- */
    .stop-label {
        /* Style for the "Bus Stop:" text */
        font-weight: 500;
        margin-right: 15px;
        text-transform: uppercase;
        font-size: 0.8em;
        letter-spacing: 0.5px;
        /* Optional: Add a simple bus icon */
        /* content: '🚌 '; */
    }

    .stop-name {
        /* Style for the actual stop name */
        font-weight: 700; /* Bold and prominent */
        font-size: 1.1em;
        /* Ensures the name takes available space */
        flex-grow: 1; 
    }

    /* ---------------------- */
    /* 4. Optional: Responsive adjustment for small screens */
    /* ---------------------- */
    @media (max-width: 480px) {
        .bus-stop-item {
            /* Stack the label and name on small screens */
            flex-direction: column;
            align-items: flex-start;
            padding: 10px 15px;
        }

        .stop-label {
            margin-bottom: 3px; /* Add space between label and name */
            margin-right: 0;
            font-size: 0.75em;
        }

        .stop-name {
            font-size: 1em;
        }
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
                        <a href="pickup_student.php" class="action-btn">
                            <i class="fa-solid fa-right-long me-2"></i>
                            শিক্ষার্থী বাসে তুলুন
                        </a>
                        <a href="drop_student.php" class="action-btn">
                            <i class="fa-solid fa-left-long me-2"></i>
                            শিক্ষার্থী বাসথেকে নামান
                        </a>
                    </div>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-md-12">
                <div class="dashboard-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2"></i>
                        আজকের উপস্থিতি সারাংশ
                    </h5>
                    <div>

                        <?php
                        $sql = "SELECT 
                                        DATE(pad.created_at) AS date,
                                        TIME(pad.pickup_time) AS pickup_time,
                                        TIME(pad.drop_time) AS drop_time,

                                        s.student_name AS student_name,
                                        s.student_id AS student_id

                                    FROM pickup_and_drop AS pad
                                    JOIN students AS s ON pad.student_id = s.id
                                    JOIN route_attendant AS ra ON pad.route_attendant_id = ra.id
                                    LEFT JOIN attendants AS att ON ra.attendant = att.id
                                    LEFT JOIN routes AS r ON ra.route = r.id
                                    LEFT JOIN buses AS b ON ra.bus = b.id
                                    LEFT JOIN drivers AS d ON ra.driver = d.id

                                    WHERE pad.route_attendant_id = :route_attendant_id AND DATE(pad.created_at) = CURDATE()

                                    ORDER BY pad.created_at DESC;
                            ";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['route_attendant_id' => $_SESSION['route_attendant_id']]);
                        $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">তারিখ</th>
                                        <th scope="col">ছাত্র</th>
                                        <th scope="col">ওঠার সময়</th>
                                        <th scope="col">নামার সময়</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (count($attendance_records) === 0) {
                                        echo '<tr><td colspan="5" class="text-center">আজকের জন্য কোনো উপস্থিতি রেকর্ড নেই।</td></tr>';
                                    } else {
                                        foreach ($attendance_records as $record) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($record['date']) . '</td>';
                                            echo '<td>' . htmlspecialchars($record['student_name']) . '<br><small>' . htmlspecialchars($record['student_id']) . '<small></td>';
                                            echo '<td>' . htmlspecialchars($record['pickup_time']) . '</td>';
                                            echo '<td>' . ($record['drop_time'] ? htmlspecialchars($record['drop_time']) : '<span class="text-danger">এখনও নামানো হয়নি</span>') . '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="dashboard-header">
                        <h5 class="mb-0">
                            <i class="fas fa-route me-2"></i>
                            রুট উপ-গন্তব্য তথ্য
                        </h5>
                        <?php
                        // এখানে রুটের উপ-গন্তব্য তথ্য প্রদর্শন করুন
                        if ($attendance_info['way'] == "to_go") {
                            $stmt = "SELECT * FROM route_sub_destinations WHERE route_id = :route_id";
                            
                        } else {
                            $stmt = "SELECT * FROM route_sub_destinations WHERE route_id = :route_id ORDER BY route_sub_destinations.id DESC";

                        }
                        $stmt = $pdo->prepare($stmt);
                        $stmt->execute([':route_id' => $attendance_info['route_id']]);

                        echo '<div class="row">'; // Optional: Container for all routes
                        $counter = 0;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            // 1. Assuming your column for the route name is 'route_name'
                            $stopName = htmlspecialchars($row['destination_name']);

                            echo'
                            <div class="col-md-4 btn m-3 btn-primary">
                                <span class="stop-label"><i class="fas fa-bus"></i> '. ++$counter . ' :</span> 
                                <span class="stop-name">'.$stopName.'</span>
                            </div>';
                        
                        }

                        echo '</div>'; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- পরিসংখ্যান কার্ড -->
            <div class="row">
                <div class="col-md-6">
                    <div class="stats-card text-center">
                        <i class="fas fa-bus stats-icon"></i>
                        <div class="stats-number"><?= htmlspecialchars($attendance_info['bus_number']) ?></div>
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
                                <p><strong>বাস:</strong> <?php echo htmlspecialchars($attendance_info['bus_number']);
                                                            if (empty($attendance_info['bus_name'])) echo '(' . $attendance_info['bus_name'] . ')'  ?></p>
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