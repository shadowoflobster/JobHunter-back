<?php
header("Access-Control-Allow-Origin: *"); // Allow all domains (you can replace * with 'http://localhost:3000' for more security)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow these methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow these headers

// Handle preflight request for OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
$mysqli = new mysqli("localhost", "root", "", "example");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT * FROM users";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    echo json_encode(["message" => "No users found."]);
}

$mysqli->close();
?>