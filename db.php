<?php
// db.php
$host = 'localhost';
$user = 'root'; // Adjust based on your setup
$password = ''; // Replace with your MySQL password
$dbname = 'example'; // Replace with your database name

$mysqli = new mysqli($host, $user, $password, $dbname);

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}