<?php
require 'db.php';

header('Content-Type: application/json');

// Fetch 10 random questions
$query = "SELECT * FROM questions ORDER BY RAND() LIMIT 10";
$result = $conn->query($query);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = [
        'id' => $row['id'],
        'question' => $row['question'],
        'options' => [
            $row['option_a'],
            $row['option_b'],
            $row['option_c'],
            $row['option_d']
        ],
        'answer' => $row['correct_answer']
    ];
}

echo json_encode($questions);
?>