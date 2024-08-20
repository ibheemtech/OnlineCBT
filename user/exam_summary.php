<?php 
include '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Check if the user has already submitted the exam
$check_query = "SELECT COUNT(*) as count FROM exam_results WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] > 0) {
    die("Exam has already been submitted.");
}

// Ensure answers are available
if (!isset($_SESSION['answers']) || empty($_SESSION['answers'])) {
    die("No answers to submit.");
}

// Insert answers into the exam_results table
$insert_query = "INSERT INTO exam_results (user_id, course_id, question_id, answer) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);

foreach ($_SESSION['answers'] as $question_id => $answer) {
    $stmt->bind_param("iiii", $user_id, $course_id, $question_id, $answer);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        die("Failed to submit answer for question ID: $question_id");
    }
}

// Clear the session variables related to the exam
unset($_SESSION['exam_start_time']);
unset($_SESSION['current_question']);
unset($_SESSION['answers']);

// Redirect to the exam complete page
header("Location: exam_complete.php");
exit();
?>
