<?php 
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type"); 
header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_NAME'];

// Create the MySQL connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
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
            job_listings.category,
            job_listings.updated_at,
            companies.name AS company_name,
            companies.profile_image AS profile_image,
            job_listings.title
        FROM 
            job_listings
        LEFT JOIN
            companies
        ON           
            job_listings.company_id=companies.id
        WHERE
            job_listings.isFeatured = 1
        LIMIT 6
        
";

$result = $mysqli->query($query);

    if ($result) {
    $jobListings = [];
    while ($row = $result->fetch_assoc()) {
        $jobListings[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $jobListings]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to fetch job listings."]);
}

$mysqli->close();
?>