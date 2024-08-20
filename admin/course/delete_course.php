<?php
session_start();
include '../db.php';

if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Course deleted successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to delete course.';
    }
    $stmt->close();

    header("Location: ../courses.php");
    exit;
} else {
    $_SESSION['error_message'] = 'Invalid course ID.';
    header("Location: courses.php");
    exit;
}

$conn->close();
?>
