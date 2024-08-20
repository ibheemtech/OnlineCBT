<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include('../includes/db.php');

// Initialize metrics array
$metrics = [];

// Queries
$queries = [
    'total_courses' => "SELECT COUNT(*) AS total_courses FROM courses",
    'total_questions' => "SELECT COUNT(*) AS total_questions FROM questions",
    'total_results' => "SELECT COUNT(*) AS total_results FROM course_results",
    'total_submissions' => "SELECT COUNT(*) AS total_submissions FROM exam_results",
    'total_users' => "SELECT COUNT(*) AS total_users FROM users"
];

foreach ($queries as $key => $query) {
    $result = $conn->query($query);
    if ($result) {
        $metrics[$key] = $result->fetch_assoc()[$key];
    } else {
        $metrics[$key] = "Error: " . $conn->error;
    }
}

echo json_encode($metrics);
?>
