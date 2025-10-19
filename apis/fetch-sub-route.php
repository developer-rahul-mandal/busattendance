<?php
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['route_id'])) {
    $route_id = $_POST['route_id'] ?? '';

    if (empty($route_id)) {
        echo json_encode(['status' => 'error', 'message' => 'রুট আইডি প্রয়োজন।']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, destination_name FROM route_sub_destinations WHERE route_id = :route_id ORDER BY sequence_order ASC");
        $stmt->execute(['route_id' => $route_id]);
        $sub_routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($sub_routes) {
            echo json_encode(['status' => 'success', 'data' => $sub_routes]);
        } else {
            echo json_encode(['status' => 'error', 'data' => null, 'message' => 'কোনো সাব-রুট পাওয়া যায়নি।']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'ডেটাবেস ত্রুটি: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'অবৈধ অনুরোধ পদ্ধতি।']);
}