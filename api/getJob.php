<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type"); 
header('Content-Type: application/json');


require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();



$key = "1234";

if (isset($_GET['jobId'])) {
    $jobId = $_GET['jobId'];
}


$db_host = $_ENV['DB_HOST'];
$db_user = $_ENV['DB_USER'];
$db_password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_NAME'];

// Create the MySQL connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
$query = "
    SELECT
    job_listings.title,
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
    companies.email AS company_email
FROM 
    job_listings
LEFT JOIN
    companies
ON           
    job_listings.company_id = companies.id
WHERE 
    job_listings.id = ?;

" ;
    
$stmt = $mysqli->prepare($query);

        if (!$stmt) {
            echo json_encode(["error" => "Error preparing the query: " . $mysqli->error]);
            exit;
        }
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $jobInfo = $result->fetch_assoc();

    echo json_encode(["job" => $jobInfo]);
}else {
    echo json_encode(["error" => "No jpb found with this ID: $jobId"]);
}
$stmt->close();

$mysqli->close();

