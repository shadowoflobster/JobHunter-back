<?php 
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type"); 
header('Content-Type: application/json');


$mysqli = new mysqli("localhost", "root", "", "example");
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
            companies.name AS company_name,
            job_listings.title
        FROM 
            job_listings
        LEFT JOIN
            companies
        ON           
            job_listings.company_id=companies.id
";
$stmt = $mysqli->prepare($query);

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