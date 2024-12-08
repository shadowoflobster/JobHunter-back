<?php
if ($_SERVER['REQUEST_URI'] === '/api/user') {
    require_once './api/user.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}