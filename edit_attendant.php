<?php
session_start();
require_once 'config/database.php';

// সেশন চেক করুন
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// মহিলা পরিচারিকা ID চেক করুন
$attendant_id = (int)($_GET['id'] ?? 0);
if ($attendant_id <= 0) {
    header('Location: attendant_list.php');
    exit();
}

// মহিলা পরিচারিকা তথ্য আনুন
try {
    $stmt = $pdo->prepare("SELECT * FROM attendants WHERE id = :id");
    $stmt->execute([':id' => $attendant_id]);
    $attendant = $stmt->fetch();
    
    if (!$attendant) {
        $_SESSION['error_message'] = "মহিলা পরিচারিকা পাওয়া যায়নি";
        header('Location: attendant_list.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "সিস্টেমে সমস্যা হয়েছে";
    header('Location: attendant_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>মহিলা পরিচারিকা সম্পাদনা - বাস উপস্থিতি ব্যবস্থাপনা</title>
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
                        <i class="fas fa-edit attendant-icon"></i>
                        <h3>মহিলা পরিচারিকা সম্পাদনা করুন</h3>
                        <p>মহিলা পরিচারিকার তথ্য আপডেট করুন</p>
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

                    <form action="process_edit_attendant.php" method="POST">
                        <input type="hidden" name="attendant_id" value="<?php echo $attendant['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendant_name" class="form-label">মহিলা পরিচারিকার নাম <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="attendant_name" name="attendant_name" value="<?php echo htmlspecialchars($attendant['attendant_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendant_id_number" class="form-label">মহিলা পরিচারিকা আইডি নম্বর <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="attendant_id_number" name="attendant_id_number" value="<?php echo htmlspecialchars($attendant['attendant_id_number']); ?>" readonly required>
                                    <div class="form-text">যেমন: DR-001, DR-002</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">ফোন নম্বর <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($attendant['phone']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">ইমেইল</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($attendant['email']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="experience_years" class="form-label">অভিজ্ঞতা (বছর)</label>
                                    <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" max="50" value="<?php echo $attendant['experience_years']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary" class="form-label">বেতন</label>
                                    <input type="number" class="form-control" id="salary" name="salary" min="0" value="<?php echo $attendant['salary']; ?>">
                                    <div class="form-text">মাসিক বেতন (টাকা)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">ঠিকানা</label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="মহিলা পরিচারিকাের পূর্ণ ঠিকানা..."><?php echo htmlspecialchars($attendant['address']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">অবস্থা</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="active" value="active" <?php echo $attendant['status'] === 'active' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="active">
                                                সক্রিয়
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="inactive" value="inactive" <?php echo $attendant['status'] === 'inactive' ? 'checked' : ''; ?>>
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
                            <a href="attendant_list.php" class="btn btn-secondary">
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
