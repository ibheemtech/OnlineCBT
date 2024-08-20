<?php
// Include database connection
require '../includes/db.php'; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    // Check if the username exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo 'available';
    } else {
        echo 'not_available';
    }

    $stmt->close();
}
?>
