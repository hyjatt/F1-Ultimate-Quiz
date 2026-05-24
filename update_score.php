<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $score = (int)$_POST['score'];
    $user_id = $_SESSION['user_id'];

    if ($score > 0) {
        $stmt = $conn->prepare("UPDATE users SET super_license_points = super_license_points + ? WHERE id = ?");
        $stmt->bind_param("ii", $score, $user_id);
        $stmt->execute();
        echo "Success";
    }
}
?>