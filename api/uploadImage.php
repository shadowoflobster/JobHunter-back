<?php
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); 
header('Access-Control-Allow-Headers: Content-Type, Authorization'); 
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Your actual PHP logic continues below...

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Cloudinary\Cloudinary;



// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_NAME'];

$key="1234";

// Create the MySQL connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

$header=apache_request_headers();

$authHeader = $header['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (!isset($header['Authorization'])) {
    echo json_encode(["success" => false, "error" => "Authorization header missing"]);
    exit;
}



try{
    $decoded=JWT::decode($token, new KEY($key, 'HS256'));
    $userId=$decoded->user_id;
}catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit;
}
$userRole = $decoded->user_role;

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
        $result = $cloudinary->uploadApi()->upload($filePath,['public_id'=>$name]);
        switch ($userRole){
            case 'user':
                $query = "UPDATE user_info SET profile_image = ? WHERE user_id = ?";
                break;
            case 'company':
                $query = "UPDATE companies SET profile_image = ? WHERE companies.id = ?";
                break;
        }
        
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
