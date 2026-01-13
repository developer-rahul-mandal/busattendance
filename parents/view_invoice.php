<?php
session_start();
require_once '../config/database.php';
// সেশন চেক করুন
if (!isset($_SESSION['parent_logged_in']) || $_SESSION['parent_logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

// সেশন টাইমআউট চেক করুন (২৪ ঘন্টা)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// চালান আইডি গ্রহণ করুন

$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // চালান তথ্য সংগ্রহ করুন
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = :id");
    $stmt->execute(['id' => $invoice_id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        $_SESSION['error_message'] = "Invoice not found.";
        header('Location: dashboard.php');
        exit();
    }

    // ছাত্রের তথ্য সংগ্রহ করুন
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $invoice['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $_SESSION['error_message'] = "Student not found.";
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "DB error happend!";
    header('Location: dashboard.php');
    exit();
}

// রেজরপে SDK
require_once __DIR__ . '/../razorpay/Razorpay.php';
use Razorpay\Api\Api;

// রেজরপে কী (environment preferred)
$razorpayKey = getenv('RAZORPAY_API_KEY') ?: '';
$razorpaySecret = getenv('RAZORPAY_API_SECRET') ?: '';

// create payments table if not exists
try {
	$createPaymentsTable = "CREATE TABLE IF NOT EXISTS payments (
		id INT AUTO_INCREMENT PRIMARY KEY,
		invoice_id INT NOT NULL,
		razorpay_order_id VARCHAR(255),
		razorpay_payment_id VARCHAR(255),
		razorpay_signature VARCHAR(255),
		amount DECIMAL(10,2) DEFAULT 0,
		currency VARCHAR(10) DEFAULT 'INR',
		status VARCHAR(50) DEFAULT 'created',
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
	$pdo->exec($createPaymentsTable);
} catch (PDOException $e) {
	die('DB error: ' . $e->getMessage());
}

// If invoice already paid
if ($invoice['payment_status'] === 'unpaid') {

// Handle POST callback verification from Razorpay
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
        $razorpay_payment_id = $_POST['razorpay_payment_id'];
        $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
        $razorpay_signature = $_POST['razorpay_signature'] ?? '';

        if (empty($razorpayKey) || empty($razorpaySecret)) {
            $_SESSION['error_message'] = 'PAYMENT GATEWAY NOT WORKING, PLEASE CONTACT WITH ADMIN.';
            header('Location: view_invoice.php?id=' . $invoice_id);
            exit();
        }

        try {
            $api = new Api($razorpayKey, $razorpaySecret);

            // verify signature
            $attributes = [
                'razorpay_order_id' => $razorpay_order_id,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_signature' => $razorpay_signature
            ];
            $api->utility->verifyPaymentSignature($attributes);

            // verification passed -> record payment and update invoice
            $pdo->beginTransaction();
            $updatePayment = $pdo->prepare("UPDATE payments SET razorpay_payment_id = :pid, razorpay_signature = :sig, status = 'paid', amount = :amount, currency = :currency WHERE razorpay_order_id = :order_id");
            $updatePayment->execute([
                ':pid' => $razorpay_payment_id,
                ':sig' => $razorpay_signature,
                ':amount' => $invoice['amount'],
                ':currency' => 'INR',
                ':order_id' => $razorpay_order_id
            ]);

            $updateInvoice = $pdo->prepare("UPDATE invoices SET payment_status = 'paid' WHERE id = :id");
            $updateInvoice->execute([':id' => $invoice_id]);

            $pdo->commit();

            // show success
            $_SESSION['success_message'] = 'Payment successful. Invoice updated to paid.';
            header('Location: view_invoice.php?id=' . $invoice_id);
            // echo "<p><a href=\"view_invoice.php?id={$invoice_id}\">Back to Invoice</a></p>";
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error_message'] = 'Payment verification failed. Please try again.';
            header('Location: view_invoice.php?id=' . $invoice_id);

            // echo ": " . $e->getMessage();
            exit();
        }
    }

    // Otherwise (GET) create Razorpay order and show checkout
    if (empty($razorpayKey) || empty($razorpaySecret)) {
       $_SESSION['error_message'] = 'PAYMENT GATEWAY NOT WORKING, PLEASE CONTACT WITH ADMIN.';
        header('Location: view_invoice.php?id=' . $invoice_id);
        exit();
    }

    try {
        $api = new Api($razorpayKey, $razorpaySecret);
        // amount in paise
        $amount_paise = (int)$invoice['amount'] * 100;

        $orderData = [
            'receipt' => 'inv_' . $invoice_id,
            'amount' => $amount_paise,
            'currency' => 'INR',
            'payment_capture' => 1
        ];
        $order = $api->order->create($orderData);
        $razorpay_order_id = $order['id'];

        // insert payment record
        $insertPayment = $pdo->prepare("INSERT INTO payments (invoice_id, razorpay_order_id, amount, currency, status) VALUES (:invoice_id, :order_id, :amount, :currency, 'created')");
        $insertPayment->execute([
            ':invoice_id' => $invoice_id,
            ':order_id' => $razorpay_order_id,
            ':amount' => $invoice['amount'],
            ':currency' => 'INR'
        ]);

    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error creating Razorpay order!';
        header('Location: view_invoice.php?id=' . $invoice_id);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEW INVOICE - BUS ATTENDANCE SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>

    <div class="container mt-5">
        <h2 class="mb-4">Invoice Details</h2>
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
        <table class="table table-bordered">
            <tr>
                <th>Invoice ID</th>
                <td><?php echo htmlspecialchars($invoice['id']); ?></td>
            </tr>
            <tr>
                <th>Student Name</th>
                <td><?php echo htmlspecialchars($student['student_name']); ?></td>
            </tr>
            <tr>
                <th>Invoice Date</th>
                <td><?php echo htmlspecialchars($invoice['invoice_date']); ?></td>
            </tr>
            <tr>
                <th>Amount (INR)</th>
                <td><?php echo htmlspecialchars($invoice['amount']); ?></td>
            </tr>
            <tr>
                <th>Payment Status</th>
                <td><?php
                    if ($invoice['payment_status'] == 'paid') {
                        echo '<span class="text-success">Paid</span>';
                    } else {
                        echo '<span class="text-danger">Unpaid</span>';
                    }
                ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?php echo htmlspecialchars(ucfirst($invoice['status'])); ?></td>
            </tr>
        </table>
        <div class="container-fluied mt-3 text-center">
        <?php if ($invoice['payment_status'] == 'unpaid'): ?>
            <div class="alert alert-warning" role="alert">
                This invoice is unpaid. Please make the payment at your earliest convenience.
            </div>
            <button id="rzp-button" class="btn btn-success">Pay Now</button>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>

        <form id="payment-form" method="POST" style="display:none;">
		<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
		<input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
		<input type="hidden" name="razorpay_signature" id="razorpay_signature">
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($invoice_id); ?>">
	</form>
    </div>

    <script>
		var options = {
			"key": "<?php echo htmlspecialchars($razorpayKey); ?>",
			"amount": "<?php echo $amount_paise; ?>",
			"currency": "INR",
			"name": "Bus Attendance",
			"description": "Invoice #<?php echo htmlspecialchars($invoice_id); ?>",
			"order_id": "<?php echo htmlspecialchars($razorpay_order_id); ?>",
			"handler": function (response){
				document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
				document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
				document.getElementById('razorpay_signature').value = response.razorpay_signature;
				document.getElementById('payment-form').submit();
			},
            "method": {
                "netbanking": true,
                "card": false,
                "emi": false,
                "upi": true,
                "wallet": true
            },
			"prefill": {
				"name": "",
				"email": ""
			},
			"theme": {
				"color": "#528FF0"
			}
		};

		var rzp = new Razorpay(options);
		document.getElementById('rzp-button').onclick = function(e){
			rzp.open();
			e.preventDefault();
		}
	</script>
</body>
</html>
