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
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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

        <div id="scanner-container" class="text-center mt-3">
            <video id="preview" style="width:90%; max-width:400px; aspect-ratio: 1 / 1; object-fit:cover; border:2px solid #667eea; border-radius:10px;"></video>
            <div id="result" class="mt-3"></div>
            <button class="btn btn-danger mt-2" onclick="window.location.reload()">পুনরায় স্ক্যান করুন</button>
        </div>
    </div>
    <script type="module">
        import {
            BrowserQRCodeReader
        } from "https://cdn.jsdelivr.net/npm/@zxing/browser@latest/+esm";

        const video = document.getElementById('preview');
        const output = document.getElementById('result');
          const stopBtn = document.getElementById('scanAnotherBtn');

        const codeReader = new BrowserQRCodeReader();
        let isScanning = true;

        // Start scanning
        codeReader.decodeFromVideoDevice(null, video, (result, err) => {
            if (result && isScanning) {
                const qrData = result.getText();
                // output.innerHTML = `<div class="alert alert-success">✅ স্ক্যান সফল: ${qrData}</div>`;
                // stop camera after successful scan
                // Send scanned data to server
                fetch('process_pickup.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: JSON.stringify({
                            student_qr: qrData
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            output.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        } else {
                            output.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        output.innerHTML = `<div class="alert alert-danger">⛔ সার্ভার ত্রুটি: ${error.message}</div>`;
                    });
                isScanning = false;
                codeReader.reset();
            }
        });

        // Stop scanning manually
        //   stopBtn.onclick = () => {
        //     codeReader.reset();
        //     output.innerHTML = `<div class="alert alert-warning">⛔ স্ক্যান বন্ধ করা হয়েছে</div>`;
        //   };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>