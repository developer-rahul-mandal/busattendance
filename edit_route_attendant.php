<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$route_attendent_id = (int)($_GET['id'] ?? 0);
if ($route_attendent_id <= 0) {
    header('Location: route_attendant_list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM route_attendant WHERE id = :id");
    $stmt->execute([':id' => $route_attendent_id]);
    $route_attendent = $stmt->fetch();
    
    if (!$route_attendent) {
        $_SESSION['error_message'] = "রেকর্ড পাওয়া যায়নি";
        header('Location: edit_route_attendant.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে";
    header('Location: edit_route_attendant.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রুট পরিচারিকা যোগ করুন - বাস উপস্থিতি ব্যবস্থাপনা</title>
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
        .attendant-icon {
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
                        <i class="fa-solid fa-handshake attendant-icon"></i>
                        <h3>রুট পরিচারিকা আপডেট করুন</h3>
                        <p>রুট পরিচারিকার তথ্য প্রবেশ করে রুট পরিচারিকা সিস্টেমে আপডেট করুন</p>
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

                    <form action="process_edit_route_attendant.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($route_attendent['id']); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="way" class="form-label">পথ <span class="text-danger">*</span></label>
                                    <select name="way" id="way" class="form-control" required>
                                        <option value="">নির্বাচন করুন</option>
                                        <option value="to_go" <?php if ($route_attendent['way'] == 'to_go') echo 'selected'; ?>>যাওয়ার পথে</option>
                                        <option value="to_come" <?php if ($route_attendent['way'] == 'to_come') echo 'selected'; ?>>আসার পথে</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendant" class="form-label">মহিলা পরিচারিকা<span class="text-danger">*</span></label>
                                    <select name="attendant" id="attendant" class="form-control" required>
                                        <option value="">নির্বাচন করুন</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT id, attendant_name, attendant_id_number FROM attendants ORDER BY attendant_name ASC");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            if ($row['id'] == $route_attendent['attendant']) {
                                                echo '<option value="' . htmlspecialchars($row['id']) . '" selected>' . htmlspecialchars($row['attendant_name']) . ' (' . htmlspecialchars($row['attendant_id_number']) . ')</option>';
                                                continue;
                                            }
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['attendant_name']) . ' (' . htmlspecialchars($row['attendant_id_number']) . ')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bus" class="form-label">বাস<span class="text-danger">*</span></label>
                                    <select name="bus" id="bus" class="form-control" required>
                                        <option value="">নির্বাচন করুন</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT id, bus_number, bus_name, bus_type  FROM buses ORDER BY bus_number ASC");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            if ($row['id'] == $route_attendent['bus']) {
                                                echo '<option value="' . htmlspecialchars($row['id']) . '" selected>' . htmlspecialchars($row['bus_number']) . ' (' . htmlspecialchars($row['bus_name']) . ' - ' . htmlspecialchars($row['bus_type']). ')</option>';
                                                continue;
                                            }
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['bus_number']) . ' (' . htmlspecialchars($row['bus_name']) . ' - ' . htmlspecialchars($row['bus_type']). ')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="route" class="form-label">রুট<span class="text-danger">*</span></label>
                                    <select name="route" id="route" class="form-control" required>
                                        <option value="">নির্বাচন করুন</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT id, route_name, route_code FROM routes ORDER BY route_name ASC");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            if ($row['id'] == $route_attendent['route']) {
                                                echo '<option value="' . htmlspecialchars($row['id']) . '" selected>' . htmlspecialchars($row['route_name']) . ' (' . htmlspecialchars($row['route_code']). ')</option>';
                                                continue;
                                            }
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['route_name']) . ' (' . htmlspecialchars($row['route_code']). ')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="driver" class="form-label">ড্রাইভার<span class="text-danger">*</span></label>
                                    <select name="driver" id="driver" class="form-control" required>
                                        <option value="">নির্বাচন করুন</option>
                                        <?php
                                        $stmt = $pdo->query("SELECT id, driver_name, license_number FROM drivers ORDER BY driver_name ASC");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            if ($row['id'] == $route_attendent['driver']) {
                                                echo '<option value="' . htmlspecialchars($row['id']) . '" selected>' . htmlspecialchars($row['driver_name']) . ' (' . htmlspecialchars($row['license_number']) . ')</option>';
                                                continue;
                                            }
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['driver_name']) . ' (' . htmlspecialchars($row['license_number']) . ')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">অবস্থা</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="active" value="active" <?php if ($route_attendent['status'] === 'active') echo 'checked'; ?>>
                                            <label class="form-check-label" for="active">
                                                সক্রিয়
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive" <?php if ($route_attendent['status'] === 'inactive') echo 'checked'; ?>>
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
                            <a href="route_attendant_list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                তালিকায় ফিরুন
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
