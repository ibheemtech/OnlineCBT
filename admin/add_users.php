<?php
include 'db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $profile_picture = $_FILES['profile_picture']['name'];

    // Move the uploaded profile picture to the "uploads" directory
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($profile_picture);
    move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);

    // Check for existing username or email
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    if ($check_stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<div class='alert alert-danger'>Username or Email already exists.</div>";
    } else {
        // Prepare the insert statement
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, name, password, is_active, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
        if ($insert_stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters and execute the statement
        $insert_stmt->bind_param("ssssss", $username, $email, $name, $password, $is_active, $profile_picture);

        if ($insert_stmt->execute()) {
            echo "<div class='alert alert-success'>User added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error adding user: " . $conn->error . "</div>";
        }

        $insert_stmt->close();
    }
    $check_stmt->close();
}
?>

<div class="container mt-4">
    <h1>Add New User</h1>
    <a href="view_users.php" class="btn btn-primary mb-3">View User</a>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" class="form-control">
        </div>
        <div class="form-group">
            <label for="is_active">Active:</label>
            <input type="checkbox" id="is_active" name="is_active">
        </div>
        <button type="submit" class="btn btn-primary">Add User</button>
    </form>
</div>

<?php
include 'footer.php';
$conn->close();
?>
