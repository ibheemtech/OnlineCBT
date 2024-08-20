<?php
include 'db.php';
include 'header.php';

// Initialize variables
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;

// Fetch user details if ID is set
if ($user_id) {
    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT username, email, name, profile_picture, is_active FROM users WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>User not found.</div>";
    }
    $stmt->close();
}

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Check if a new profile picture is uploaded
    if (!empty($_FILES['profile_picture']['name'])) {
        $profile_picture = $_FILES['profile_picture']['name'];
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], "uploads/" . $profile_picture);
    } else {
        // Keep the current profile picture if no new one is uploaded
        $profile_picture = $user['profile_picture'];
    }
    
    // Check for existing username or email
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $check_stmt->bind_param("ssi", $username, $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "<div class='alert alert-danger'>Username or Email already exists.</div>";
    } else {
        // Prepare the update statement
        if ($password) {
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, name = ?, password = ?, is_active = ?, profile_picture = ? WHERE id = ?");
            $update_stmt->bind_param("ssssisi", $username, $email, $name, $password, $is_active, $profile_picture, $user_id);
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, name = ?, is_active = ?, profile_picture = ? WHERE id = ?");
            $update_stmt->bind_param("sssisi", $username, $email, $name, $is_active, $profile_picture, $user_id);
        }

        if (!$update_stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Execute the update
        if ($update_stmt->execute()) {
            echo "<div class='alert alert-success'>User updated successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error updating user: " . $conn->error . "</div>";
        }
        $update_stmt->close();
    }
    $check_stmt->close();
}

?>

<div class="container mt-4">
    <h1>Edit User</h1>
    <a href="view_users.php" class="btn btn-primary mb-3">View User</a>
    <?php if ($user): ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password (leave blank to keep current)">
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                <?php if ($user['profile_picture']): ?>
                    <img src="uploads/<?= htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" width="100">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="is_active">Active:</label>
                <input type="checkbox" id="is_active" name="is_active" <?= $user['is_active'] ? 'checked' : ''; ?>>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    <?php else: ?>
        <div class='alert alert-danger'>No user data available.</div>
    <?php endif; ?>
</div>

<?php
include 'footer.php';
$conn->close();
?>
