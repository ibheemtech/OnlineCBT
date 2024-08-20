<?php
include 'db.php';

// Get user ID
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        header("Location: view_users.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error deleting user: " . $conn->error . "</div>";
    }
    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>Invalid user ID.</div>";
}

$conn->close();
?>
