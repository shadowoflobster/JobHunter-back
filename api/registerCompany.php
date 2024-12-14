<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    $name = $data['name'];  
    $email = $data['email'];
    $password = $data['password'];

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $mysqli = new mysqli("localhost", "root", "", "example");

    // Check connection
    if ($mysqli->connect_error) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    // Check if the email already exists
    $query = "SELECT * FROM users WHERE email = ?";
    $query2 = "SELECT * FROM companies WHERE email = ?";
    $stmt = $mysqli->prepare($query);
    $stmt2 = $mysqli->prepare($query2);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();  
    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email is already registered as a user."]);
        $stmt->close();
         $stmt2->close();
         $mysqli->close();
        exit;
    }
    $stmt2->bind_param("s", $email);;
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($result2->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email is already registered as a company."]);
        $stmt->close();
        $stmt2->close();
        $mysqli->close();
        exit;
    }
    $stmt->close();
    $stmt2->close();
    // Insert the new user into the database
    $query = "INSERT INTO companies (name,  email, password) VALUES (?,  ?, ?)";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Registration successful."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to register user."]);
        }
        $stmt->close();
    } else {
        echo json_encode(value: ["status" => "error", "message" => "Query preparation failed."]);
    }

    // Close the database connection
    $mysqli->close();
    ?>
