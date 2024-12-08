<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type"); 
header('Content-Type: application/json');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit;
}

require 'vendor/autoload.php'; 
$header = apache_request_headers();

if(isset($header['Authorization'])){
    $authHeader = $header['Authorization'];
    $token = str_replace('Bearer ','',$authHeader);
    $key = "1234";
    try{
        $decode = JWT::decode($token, new Key($key, 'HS256'));

        echo json_encode(["message"=>"connected to user", "user"=>$decode]);
    }catch(Exception $e){
        echo json_encode(["Error"=> $e]);
    } 
    }else {
        echo json_encode(["error" => "Authorization header not found"]);
    
}


