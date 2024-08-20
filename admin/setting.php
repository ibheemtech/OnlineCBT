<?php
// Include the database connection file
include('db.php');

// Admin credentials
$username = "admin"; // Replace with the desired admin username
$name = "Admin Name"; // Replace with the desired admin name
$email = "admin@.com"; // Replace with the desired admin email
$password = "admin"; // Replace with the desired admin password
$profile_picture = "default.png"; // Replace with the desired profile picture file name (optional)

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO admins (username, name, email, profile_picture, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $name, $email, $profile_picture, $hashed_password);

// Execute the statement
if ($stmt->execute()) {
    echo "New admin inserted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
