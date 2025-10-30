<?php
// Student ID Card Generator API (with Pickup/Drop + Bus Icon + QR on Right Side)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit(0);

function sendJson($success, $data = null, $error = null) {
    http_response_code($success ? 200 : 500);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error,
        'timestamp' => time()
    ]);
    exit;
}

if (!extension_loaded('gd')) {
    sendJson(false, null, 'GD extension is not installed. Please install php-gd.');
}

function downloadImage($url) {
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0'],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $data = @file_get_contents($url, false, $context);
        if ($data !== false) return $data;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0'
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200 && $data) return $data;
    }
    return false;
}

function generateQRCode($data, $size = 120) {
    $encoded = urlencode($data);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}";
}

function loadImageFromData($data) {
    return @imagecreatefromstring($data);
}

function loadImageFromFile($filePath) {
    if (!file_exists($filePath)) return false;
    $info = @getimagesize($filePath);
    if (!$info) return false;

    switch ($info[2]) {
        case IMAGETYPE_JPEG: return @imagecreatefromjpeg($filePath);
        case IMAGETYPE_PNG: return @imagecreatefrompng($filePath);
        case IMAGETYPE_GIF: return @imagecreatefromgif($filePath);
        case IMAGETYPE_WEBP: return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : false;
        default: return false;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) sendJson(false, null, 'Invalid JSON input');

        $required = ['student_id', 'name', 'phone', 'school', 'pickup', 'drop'];
        $missing = [];
        foreach ($required as $f) if (empty($input[$f])) $missing[] = $f;
        if ($missing) sendJson(false, null, 'Missing fields: ' . implode(', ', $missing));

        $student_id = trim($input['student_id']);
        $name = trim($input['name']);
        $phone = trim($input['phone']);
        $school = trim($input['school']);
        $pickup = trim($input['pickup']);
        $drop = trim($input['drop']);
        $student_image_url = $input['student_image'] ?? '';

        $qr_data = $input['qr_data'] ??
            "student://open?student_id=" . urlencode($student_id) .
            "&name=" . urlencode($name) .
            "&phone=" . urlencode(str_replace("+91 ", "", $phone)) .
            "&school=" . urlencode($school) .
            "&pickup=" . urlencode($pickup) .
            "&drop=" . urlencode($drop);

        // Canvas
        $width = 800;
        $height = 500;
        $image = imagecreate($width, $height);
        if (!$image) throw new Exception('Failed to create image canvas');

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 41, 128, 185);
        $lightBlue = imagecolorallocate($image, 236, 240, 241);
        $darkBlue = imagecolorallocate($image, 21, 67, 96);
        $gray = imagecolorallocate($image, 200, 200, 200);

        imagefill($image, 0, 0, $white);
        imagerectangle($image, 10, 10, $width - 11, $height - 11, $blue);

        // Header background
        $headerHeight = 70;
        imagefilledrectangle($image, 15, 15, $width - 16, $headerHeight, $blue);

        // 🚌 Add bus icon on left of header
        $busIconPath = 'bus.png';
        $busX = 25;
        $busY = 22;
        if (file_exists($busIconPath)) {
            $busIcon = loadImageFromFile($busIconPath);
            if ($busIcon) {
                $busSize = 30;
                imagecopyresampled($image, $busIcon, $busX, $busY, 0, 0, $busSize, $busSize, imagesx($busIcon), imagesy($busIcon));
            } else {
                imagestring($image, 5, $busX, $busY + 5, "BUS", $white);
            }
        } else {
            imagestring($image, 5, $busX, $busY + 5, "BUS", $white);
        }

        // Header text
        imagestring($image, 5, $busX + 50, 30, "BUS ATTENDANCE SYSTEM", $white);

        // Student Photo
        $photoX = 50;
        $photoY = $headerHeight + 40;
        $photoSize = 150;

        imagefilledrectangle($image, $photoX, $photoY, $photoX + $photoSize, $photoY + $photoSize, $lightBlue);
        imagerectangle($image, $photoX, $photoY, $photoX + $photoSize, $photoY + $photoSize, $blue);

        $studentPhotoAdded = false;
        if ($student_image_url) {
            $imgData = downloadImage($student_image_url);
            if ($imgData) {
                $photo = loadImageFromData($imgData);
                if ($photo) {
                    imagecopyresampled($image, $photo, $photoX + 5, $photoY + 5, 0, 0, $photoSize - 10, $photoSize - 10, imagesx($photo), imagesy($photo));
                    $studentPhotoAdded = true;
                }
            }
        }
        if (!$studentPhotoAdded) {
            imagestring($image, 3, $photoX + 40, $photoY + 70, "No Photo", $black);
        }

        // Info text
        $infoX = $photoX + $photoSize + 40;
        $infoY = $headerHeight + 50;
        $line = 35;
        imagestring($image, 4, $infoX, $infoY, "ID: " . $student_id, $black);
        imagestring($image, 4, $infoX, $infoY + $line, "Name: " . $name, $black);
        imagestring($image, 4, $infoX, $infoY + ($line * 2), "Phone: " . $phone, $black);
        imagestring($image, 4, $infoX, $infoY + ($line * 4), "Drop location: " . $drop, $black);
        imagestring($image, 4, $infoX, $infoY + ($line * 3), "Pickup location: " . $pickup, $black);
        imagestring($image, 4, $infoX, $infoY + ($line * 5), "School: " . $school, $black);

        // QR Code on Right Side (parallel to photo)
        $qrSize = 120;
        $qrX = $width - $qrSize - 70;
        $qrY = $photoY + 20;
        $qrUrl = generateQRCode($qr_data, 150);
        $qrData = downloadImage($qrUrl);
        if ($qrData) {
            $qrImg = loadImageFromData($qrData);
            if ($qrImg) {
                imagecopyresampled($image, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));
                imagerectangle($image, $qrX - 2, $qrY - 2, $qrX + $qrSize + 1, $qrY + $qrSize + 1, $blue);
                imagestring($image, 3, $qrX - 10, $qrY + $qrSize + 10, "Scan for Attendance", $darkBlue);
            }
        } else {
            imagefilledrectangle($image, $qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize, $gray);
            imagerectangle($image, $qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize, $black);
            imagestring($image, 3, $qrX + 25, $qrY + 50, "QR", $black);
        }

        // Footer
        $footerText = "Valid Student ID - " . date('Y');
        $footerWidth = imagefontwidth(3) * strlen($footerText);
        $footerX = ($width - $footerWidth) / 2;
        imagestring($image, 3, $footerX, $height - 40, $footerText, $darkBlue);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        sendJson(true, [
            'student_id' => $student_id,
            'name' => $name,
            'base64_image' => base64_encode($imageData),
            'image_type' => 'image/png',
            'qr_data' => $qr_data,
            'student_image_used' => $studentPhotoAdded
        ]);
    } else {
        sendJson(true, [
            'api_name' => 'Student ID Card API (Bus Icon + Pickup/Drop + QR Right)',
            'version' => '3.1',
            'features' => ['Bus Icon', 'Pickup/Drop', 'QR on Right Side', 'Base64 Image Output']
        ]);
    }
} catch (Exception $e) {
    sendJson(false, null, $e->getMessage());
}
?>
