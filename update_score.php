<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $score = (int)$_POST['score'];
    $correct = isset($_POST['correct']) ? (int)$_POST['correct'] : $score;
    $difficulty = isset($_POST['difficulty']) ? $_POST['difficulty'] : 'unknown';
    $user_id = $_SESSION['user_id'];

    if ($score > 0) {
        $stmt = $conn->prepare("UPDATE users SET super_license_points = super_license_points + ? WHERE id = ?");
        $stmt->bind_param("ii", $score, $user_id);
        $stmt->execute();
    }
    
    // Log the quiz completion with the accurate breakdown
    logUserAction($conn, $user_id, "Completed $difficulty quiz: $correct/10 correct. Earned $score pts.");
    echo "Success";
}
?>