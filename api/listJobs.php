<?php 
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type"); 
header('Content-Type: application/json');


require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_NAME'];

// Create the MySQL connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $mysqli->connect_error]);
    exit();
}

    $query = "
    SELECT 
        job_listings.id, 
        job_listings.description, 
        job_listings.salary, 
        job_listings.minSalary, 
        job_listings.maxSalary,
        job_listings.requirements, 
        job_listings.currency, 
        job_listings.location, 
        job_listings.updated_at,
        job_listings.category,
        companies.name AS company_name,
        companies.profile_image AS profile_image,
        job_listings.title
    FROM 
        job_listings
    LEFT JOIN
        companies
    ON           
        job_listings.company_id=companies.id
";

if(isset($_GET['category']) && !empty($_GET['category']) && $_GET['category']!='undefined'){
    $category=$_GET['category'];
    $query .= "WHERE job_listings.category = ?";
    
}

$stmt = $mysqli->prepare($query);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Query preparation failed: " . $mysqli->error]);
    exit();
}

if (isset($category)) {
    $stmt->bind_param('s', $category);
}

$stmt->execute();

$result = $stmt->get_result();

    if ($result) {
    $jobListings = [];
    while ($row = $result->fetch_assoc()) {
        $jobListings[] = $row;
    }
    echo json_encode( ["status" => "success", "data" => $jobListings]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to fetch job listings."]);
}

$mysqli->close();
?>