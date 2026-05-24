<?php
$host = 'localhost';
$db = 'f1_quiz';
$user = 'root'; // Default XAMPP username
$pass = '';     // Default XAMPP password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>