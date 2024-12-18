<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data['title']) ||  empty($data['description']) ||

    (empty($data['salary']) && (empty($data['minSalary']) && empty($data['maxSalary']))) //Binary opeartion checks if all salary fields are empty
    
    || empty($data['currency']) || empty($data['location']) || empty($data['company_id']) || empty($data['job_type'])){
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        echo json_encode($data['title'],$data['description'],$data['salary'],$data['minSalary'],$data['maxSalary'],$data['currency'],$data['location'],$data['company_id'],$data['jobType']);
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
    $job_type = $data['job_type'];
    $types = 'sssssss';

    $mysqli = new mysqli("localhost", "root", "", "example");

    if ($mysqli->connect_error) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    $query = "INSERT INTO job_listings (title, description, requirements, currency, location, company_id, job_type";


    //Query takes columns only if specific requirements exist
    if($salary !== null){
        $query .= ", salary";
    }
    if($minSalary !== null){
        $query .= ", minSalary";
    }
    if($maxSalary !== null){
        $query .= ", maxSalary";
    }

    $query.=") VALUES (?,?,?,?,?,?,?";

    //Query takes values only if specific requirements exist
    if($salary !== null){
        $query .= ", ?";
    }
    if($minSalary !== null){
        $query .= ", ?";
    }
    if($maxSalary !== null){
        $query .= ", ?";
    }
    $query .= ")";

    $stmt=$mysqli->prepare($query);
    if($stmt){
        $params = [ $title, $description, $requirements, $currency, $location, $company_id, $job_type];
        $types = 'sssssss';
        if ($salary !== null) {
            $types .='s';
            $params[]=$salary;
        }
        if ($minSalary !== null) {
            $types .='s';
            $params[]=$minSalary;
        } 
        if ($maxSalary !== null) {
            $types .='s';
            $params[]=$maxSalary;
        }
        $stmt->bind_param($types, ...$params);


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