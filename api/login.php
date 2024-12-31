<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type"); 
header('Content-Type: application/json');



// Load the Firebase JWT library
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
try{
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(["error" => "All fields are required."]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

$mysqli = new mysqli("localhost", "root", "", "example");

// Check connection
if ($mysqli->connect_error) {
    die(json_encode(["error" => "Error connecting to the database: " . $mysqli->connect_error]));
}

// Secure query with prepared statements
$query = " SELECT 'user' AS type, id, name, password, email, role 
    FROM users 
    WHERE email = ? 
    UNION 
    SELECT 'company' AS type, id, name, password, email, role 
    FROM companies 
    WHERE email = ?;";

$stmt = $mysqli->prepare($query);

if ($stmt) {
    // Bind the parameter
    $stmt->bind_param("ss", $email,$email   );

    // Execute the query
    $stmt->execute();

    // Fetch the result
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Generate JWT Token
            $key = "1234"; // Replace with a strong secret key
            $payload = [
                "iss" => "yourdomain.com", // Issuer
                "aud" => "yourdomain.com", // Audience
                "iat" => time(),           // Issued at
                "exp" => time() + 3600,    // Token expires in 1 hour
                "user_id" => $user['id'],  // Include user information in the token
                "name" => $user['name'],   // Name of the user
                "email" => $user['email'],  // Add additional claims if necessary
                "user_role" => $user['role'], //
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            $header = apache_request_headers();
            
            
            echo json_encode(value: ["message" => "Login successful!", "token" => $jwt,"id"=>$user['id'],"role" => $user['role']]);

        } else {
            echo json_encode(["error" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["error" => "No user found with this email."]);
    }
    // Close the statement
    $stmt->close();
} else {
    echo json_encode(["error" => "Error preparing the query: " . $mysqli->error]);
}

// Close the connection
$mysqli->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}