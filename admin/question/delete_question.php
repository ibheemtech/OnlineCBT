<?php
include '../db.php';

// Ensure the ID is set and valid
if (isset($_GET['id'])) {
    $question_id = intval($_GET['id']);

    // Prepare and execute the delete statement
    $delete_stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
    $delete_stmt->bind_param("i", $question_id);
    
    if ($delete_stmt->execute()) {
        // Redirect back to the view questions page with success message
        header("Location: view_question.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error deleting question: " . $conn->error . "</div>";
    }
    $delete_stmt->close();
} else {
    echo "<div class='alert alert-danger'>Invalid question ID.</div>";
}

$conn->close();
?>
