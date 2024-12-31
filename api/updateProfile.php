<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

require 'vendor/autoload.php';

$data = json_decode(file_get_contents("php://input"), true);
$header = apache_request_headers();
$key = "1234";
$mysqli = new mysqli("localhost", "root", "", "example");

if ($mysqli->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

if (!isset($header['Authorization'])) {
    echo json_encode(["success" => false, "error" => "Authorization header missing"]);
    exit;
}

$authHeader = $header['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $userId = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit;
}

$allowedFields = ["position", "address", "website", "about_me", "skills"];
$updates = [];
$types = "";
$values = [];

foreach ($allowedFields as $field) {
    if (isset($data[$field]) && !empty($data[$field])) {
        $value = $data[$field];
        
        if ($field === "skills") {
            if (!is_array($value)) {
                echo json_encode(["success" => false, "error" => "Skills must be an array"]);
                exit;
            }
            $value = json_encode($value);
        }
        
        $updates[] = "$field = ?";
        $types .= "s";
        $values[] = $value;
    }
}

if (empty($updates)) {
    echo json_encode(["success" => false, "error" => "No valid fields to update"]);
    exit;
}

$query = "UPDATE user_info SET " . implode(", ", $updates) . " WHERE user_id = ?";
$types .= "i";
$values[] = $userId;

$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Failed to prepare statement"]);
    exit;
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
} else {
    echo json_encode(["success" => false, "error" => "Update failed"]);
}

$stmt->close();
$mysqli->close();