<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$key = "1234";

$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_NAME'];

// Create the MySQL connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) {
    die(json_encode(["error" => "Error connecting to the database: " . $mysqli->connect_error]));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit();
}
$data = json_decode(file_get_contents("php://input"), true);




    try{
        
        if(!isset($data['user_id']) || !isset($data['user_role'])){
            echo json_encode(["error" => "No user_id or user_role"]);
            exit();
        }
    
    $userId=$data['user_id'];
    $userRole=$data['user_role'];

    if (!in_array($userRole, ['user', 'company'])) {
        echo json_encode(["error" => "Invalid user role"]);
        exit();
    }

    switch ($userRole){
        case 'user':
            $query = "
            SELECT
                user_info.profile_image
            FROM
                user_info
            WHERE
                user_info.user_id = ?
            ";
            break;
        case 'company':
            $query = "
            SELECT 
                companies.profile_image
            FROM
                companies
            WHERE
                companies.id = ?
            ";
            break;
        }

        $stmt = $mysqli->prepare($query);

        if(!$stmt){
            echo json_encode(["Error" => "Error preparing statement. " . $mysqli->error]);
            exit;
        }

        $stmt->bind_param('i',$userId);
        $stmt->execute();
        $result=$stmt->get_result();

        if($result){
                $imgUrl=$result->fetch_assoc();
            
            echo json_encode( ["status" => "success", "data" => $imgUrl]);

        }
        else{
            echo json_encode(["status" => "error", "message" => "Failed to fetch image url."]);

        }


}catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}


$mysqli->close();



?>