<?php
include '../db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $course_id = $_GET['id'];
    $new_status = $_GET['status'];

    $stmt = $conn->prepare("UPDATE courses SET is_active = ? WHERE course_id = ?");
    $stmt->bind_param("ii", $new_status, $course_id);
    if ($stmt->execute()) {
        $message = 'Course status updated successfully!';
    } else {
        $message = 'Failed to update course status.';
    }
    $stmt->close();

    // Redirect with success message
    header("Location:../courses.php?message=" . urlencode($message));
    exit;
} else {
    header("Location: ../courses.php?message=" . urlencode('Invalid request.'));
    exit;
}

$conn->close();
?>

