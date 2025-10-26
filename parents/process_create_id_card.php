<?php
// Enhanced Student ID Card API with QR Code at Bottom
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Simple response function
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

// Check if GD is available
if (!extension_loaded('gd')) {
    sendJson(false, null, 'GD extension is not installed. Please install php-gd.');
}

// Function to download image from URL
function downloadImage($url) {
    // Try file_get_contents first
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        $imageData = @file_get_contents($url, false, $context);
        if ($imageData !== false) {
            return $imageData;
        }
    }
    
    // Fallback to cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $imageData) {
            return $imageData;
        }
    }
    
    return false;
}

// Function to generate QR code URL
function generateQRCode($data, $size = 120) {
    $encodedData = urlencode($data);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
}

// Function to load image from data
function loadImageFromData($imageData) {
    $image = @imagecreatefromstring($imageData);
    return $image;
}

function loadImageFromFile($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $imageInfo = @getimagesize($filePath);
    if (!$imageInfo) {
        return false;
    }
    
    switch($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            return @imagecreatefromjpeg($filePath);
        case IMAGETYPE_PNG:
            return @imagecreatefrompng($filePath);
        case IMAGETYPE_GIF:
            return @imagecreatefromgif($filePath);
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                return @imagecreatefromwebp($filePath);
            }
            return false;
        default:
            return false;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJson(false, null, 'Invalid JSON input');
        }

        // Validate required fields
        $required = ['student_id', 'name', 'phone', 'school'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            sendJson(false, null, 'Missing fields: ' . implode(', ', $missing));
        }

        // Prepare student data
        $student_id = trim($input['student_id']);
        $name = trim($input['name']);
        $phone = trim($input['phone']);
        $school = trim($input['school']);
        $student_image_url = $input['student_image'] ?? '';
        
        // Generate QR code data
        $qr_data = $input['qr_data'] ?? "student://open?student_id=" . urlencode($student_id) . 
                                        "&name=" . urlencode($name) . 
                                        "&phone=" . urlencode(str_replace("+91 ", "", $phone)) . 
                                        "&school=" . urlencode($school);

        // Create image canvas
        $width = 800;
        $height = 500;
        $image = imagecreate($width, $height);
        
        if (!$image) {
            throw new Exception('Failed to create image canvas');
        }

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 41, 128, 185);
        $lightBlue = imagecolorallocate($image, 236, 240, 241);
        $darkBlue = imagecolorallocate($image, 21, 67, 96);
        $gray = imagecolorallocate($image, 200, 200, 200);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Add border
        imagerectangle($image, 10, 10, $width-11, $height-11, $blue);
        imagerectangle($image, 11, 11, $width-12, $height-12, $blue);

        // Header section
        $headerHeight = 70;
        imagefilledrectangle($image, 15, 15, $width-16, $headerHeight, $blue);
        // Load and add bus icon
        $busIconPath = 'bus.png';
        if (file_exists($busIconPath)) {
            $busIcon = loadImageFromFile($busIconPath);
            if ($busIcon) {
                $busSize = 30; // Size of the bus icon
                imagecopyresampled($image, $busIcon, 
                                20, 25, // Position (x, y)
                                0, 0, 
                                $busSize, $busSize, 
                                imagesx($busIcon), imagesy($busIcon));
            } else {
                // Fallback to text if image fails to load
                imagestring($image, 5, 20, 30, "BUS", $white);
            }
        } else {
            // Fallback to text if file doesn't exist
            imagestring($image, 5, 20, 30, "BUS", $white);
        }
        imagestring($image, 5, 60, 30, "BUS ATTENDANCE SYSTEM", $white);
        imagestring($image, 5, 20, 30, "🚌", $white);

        // Student photo area (left side - larger since we have more space)
        $photoX = 50;
        $photoY = $headerHeight + 40;
        $photoSize = 150;

        // Photo background
        imagefilledrectangle($image, $photoX, $photoY, $photoX + $photoSize, $photoY + $photoSize, $lightBlue);
        imagerectangle($image, $photoX, $photoY, $photoX + $photoSize, $photoY + $photoSize, $blue);

        // Load and add student photo
        $studentPhotoAdded = false;
        if (!empty($student_image_url)) {
            $studentImageData = downloadImage($student_image_url);
            if ($studentImageData) {
                $studentPhoto = loadImageFromData($studentImageData);
                if ($studentPhoto) {
                    // Resize and place student photo
                    imagecopyresampled($image, $studentPhoto, 
                                     $photoX + 5, $photoY + 5, 
                                     0, 0, 
                                     $photoSize - 10, $photoSize - 10, 
                                     imagesx($studentPhoto), imagesy($studentPhoto));
                    $studentPhotoAdded = true;
                }
            }
        }

        // Add placeholder text if no student photo
        if (!$studentPhotoAdded) {
            imagestring($image, 3, $photoX + 40, $photoY + ($photoSize/2) - 10, "Student", $black);
            imagestring($image, 3, $photoX + 50, $photoY + ($photoSize/2) + 5, "Photo", $black);
        }

        // Student information area (right of photo)
        $infoX = $photoX + $photoSize + 40;
        $infoY = $headerHeight + 50;
        $lineHeight = 35;

        // Student information
        imagestring($image, 4, $infoX, $infoY, "ID: " . $student_id, $black);
        imagestring($image, 4, $infoX, $infoY + $lineHeight, "Name: " . $name, $black);
        imagestring($image, 4, $infoX, $infoY + ($lineHeight * 2), "Phone: " . $phone, $black);
        imagestring($image, 4, $infoX, $infoY + ($lineHeight * 3), "School: " . $school, $black);

        // QR Code area (bottom center)
        $qrSize = 100;
        $qrX = ($width - $qrSize) / 2;  // Center horizontally
        $qrY = $height - 150;  // Position from bottom

        // Generate and add QR code
        $qrCodeUrl = generateQRCode($qr_data, 120);
        $qrImageData = downloadImage($qrCodeUrl);
        
        if ($qrImageData) {
            $qrImage = loadImageFromData($qrImageData);
            if ($qrImage) {
                imagecopyresampled($image, $qrImage, 
                                 $qrX, $qrY, 
                                 0, 0, 
                                 $qrSize, $qrSize, 
                                 imagesx($qrImage), imagesy($qrImage));
                
                // QR Code border
                imagerectangle($image, $qrX-2, $qrY-2, $qrX + $qrSize + 1, $qrY + $qrSize + 1, $blue);
            }
        } else {
            // QR code placeholder
            imagefilledrectangle($image, $qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize, $gray);
            imagerectangle($image, $qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize, $black);
            imagestring($image, 3, $qrX + 25, $qrY + ($qrSize/2) - 5, "QR Code", $black);
        }

        // QR Code label (below QR code)
        $labelText = "Scan for Attendance";
        $labelWidth = imagefontwidth(3) * strlen($labelText);
        $labelX = ($width - $labelWidth) / 2;
        imagestring($image, 3, $labelX, $qrY + $qrSize + 10, $labelText, $darkBlue);

        // Footer (below QR code label)
        $footerText = "Valid Student ID - " . date('Y');
        $footerWidth = imagefontwidth(3) * strlen($footerText);
        $footerX = ($width - $footerWidth) / 2;
        imagestring($image, 3, $footerX, $qrY + $qrSize + 35, $footerText, $darkBlue);

        // Capture image
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
        // GET request - API info
        sendJson(true, [
            'api_name' => 'Enhanced Student ID Card API - QR at Bottom',
            'version' => '2.1',
            'features' => ['QR Code at Bottom', 'Student Image', 'Professional Design'],
            'gd_info' => function_exists('gd_info') ? 'Available' : 'Not Available'
        ]);
    }

} catch (Exception $e) {
    sendJson(false, null, $e->getMessage());
}
?>