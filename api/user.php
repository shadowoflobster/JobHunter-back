<?php
header("Access-Control-Allow-Origin: *"); // Allow all domains (you can replace * with 'http://localhost:3000' for more security)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow these methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow these headers

// Handle preflight request for OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_NAME'];

// Create the MySQL connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
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