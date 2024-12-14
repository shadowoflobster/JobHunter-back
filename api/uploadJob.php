<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data['title']) ||  empty($data['description']) ||

    (empty($data['salary']) && (empty($data['minSalary']) && empty($data['maxSalary']))) //Binary opeartion checks if all salary fields are empty
    
    || empty($data['currency']) || empty($data['location']) || empty($data['company_id'])){
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }



    $title=$data['title'];
    $description=$data['description'];
    
    $requirements=!empty($data['requirements']) ? json_encode($data['requirements']) :null;
    
    $salary=!empty($data['salary']) ? $data['salary'] :null;
    $minSalary=!empty($data['minSalary']) ? $data['minSalary'] :null;
    $maxSalary=!empty($data['maxSalary']) ? $data['maxSalary'] :null;
 
    $currency=$data['currency'];
    $location=$data['location'];
    $company_id = $data['company_id'];

    $mysqli = new mysqli("localhost", "root", "", "example");

    if ($mysqli->connect_error) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    $query = "INSERT INTO job_listings (title, description, requirements, currency, location, company_id";

    if($salary !== null){
        $query .= ", salary";
    }elseif($minSalary !== null){
        $query .= ", minSalary";
    }elseif($maxSalary !== null){
        $query .= ", maxSalary";
    }

    $query.=") VALUES (?,?,?,?,?,?";
    
    if($salary !== null){
        $query .= ", ?";
    }elseif($minSalary !== null){
        $query .= ", ?";
    }elseif($maxSalary !== null){
        $query .= ", ?";
    }
    $query .= ")";

    $stmt=$mysqli->prepare($query);
    if($stmt){
        if ($salary !== null) {
            $stmt->bind_param('sssssss', $title, $description, $requirements, $currency, $location, $company_id, $salary);
        } elseif ($minSalary !== null) {
            $stmt->bind_param('sssssss', $title, $description, $requirements, $currency, $location, $company_id, $minSalary);
        } elseif ($maxSalary !== null) {
            $stmt->bind_param('sssssss', $title, $description, $requirements, $currency, $location, $company_id, $maxSalary);
        }

    if($stmt->execute()){
        echo json_encode(["status" => "success", "message" =>"Job uploaded successfuly"]);
    }else{
        echo json_encode(["status" => "error", "message" =>"Failed to upload job"]);
    }
    $stmt->close();

}else{
    echo json_encode(["status" => "error", "message" => "Query preparation failed."]);
}
$mysqli->close();
?>