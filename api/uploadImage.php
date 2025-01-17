<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;
use Cloudinary\Cloudinary;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$header = apache_request_headers();
$key='1234';
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

try{
    $decoded=JWT::decode($token, new KEY($key, 'HS256'));
    $userId=$decoded->user_id;
}catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit;
}

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $_ENV['CLOUDINARY'],
        'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
        'api_secret' => $_ENV['CLOUDINARY_SECRET'],
    ],
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload failed.']);
        exit;
    }

    $filePath = $_FILES['file']['tmp_name'];
    $name = $_POST['public_id'];
        
    try {
        $result = $cloudinary->uploadApi()->upload($filePath,['public_id'->$name]);
        $query = "UPDATE user_info SET profile_image = ? WHERE user_id = ?";
        $imageUrl = $result['secure_url'];
        $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Failed to prepare statement"]);
        exit;
    }   
       
        $stmt->bind_param('si', $imageUrl, $userId);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "secure_url" => $imageUrl]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to update database"]);
        }
        $stmt->close();



    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
}
