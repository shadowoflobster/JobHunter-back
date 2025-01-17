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

$data = json_decode(file_get_contents("php://input"), true);


require 'vendor/autoload.php';

$header = apache_request_headers();
$key = "1234";
$mysqli = new mysqli("localhost", "root", "", "example");
if ($mysqli->connect_error) {
    die(json_encode(["error" => "Error connecting to the database: " . $mysqli->connect_error]));
}






if (isset($header['Authorization'])) {
    $authHeader = $header['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);



    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        // Validate if 'user' exists in the payload
        if (!isset($decoded->user_id)) {
            echo json_encode(["error" => "User data not found in token"]);
            exit;
        }
        $userId = $decoded->user_id;

        $query = "
        SELECT 
            users.name,
            users.surname,
            users.email,
            user_info.id, 
            user_info.position,
            user_info.address,
            user_info.website,
            user_info.about_me,
            user_info.date_of_birth,
            user_info.skills,
            user_info.gender,
            user_info.mobile_number,
            user_info.profile_picture,
            user_info.profile_image
        FROM 
            user_info
        INNER JOIN
            users ON users.id = user_info.user_id
        WHERE           
            users.id = ?"
        ;

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            echo json_encode(["error" => "Error preparing the query: " . $mysqli->error]);
            exit;
        }

        $stmt->bind_param("i", $userId); // "i" for integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $userInfo = $result->fetch_assoc();

            echo json_encode(["user" => $userInfo]);
        } else {
            echo json_encode(["error" => "No user found with this ID: $userId"]);
        }

        $stmt->close();

    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Authorization header not found"]);

}

$mysqli->close();

