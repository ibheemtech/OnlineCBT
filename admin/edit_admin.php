<?php
include('header.php'); 

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $navbar_color = $_POST['navbar_color'];
    $sidebar_color = $_POST['sidebar_color'];  // Added sidebar color field
    $profile_picture = $_FILES['profile_picture']['name'];
    $password = $_POST['password'];

    // File upload logic
    if ($profile_picture) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
    } else {
        $profile_picture = $admin['profile_picture'];
    }

    // Determine if the password should be updated
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE admins SET username = ?, name = ?, email = ?, profile_picture = ?, navbar_color = ?, sidebar_color = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssi", $username, $name, $email, $profile_picture, $navbar_color, $sidebar_color, $hashed_password, $admin_id);
    } else {
        $query = "UPDATE admins SET username = ?, name = ?, email = ?, profile_picture = ?, navbar_color = ?, sidebar_color = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $username, $name, $email, $profile_picture, $navbar_color, $sidebar_color, $admin_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Admin details updated successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to update admin details. Please try again.');</script>";
    }
}

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
?>

<div class="container mt-5">
    <h2>Edit Admin Details</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="navbar_color">Navbar Color</label>
            <input type="color" class="form-control" id="navbar_color" name="navbar_color" value="<?php echo htmlspecialchars($admin['navbar_color']); ?>">
        </div>
        <div class="form-group">
            <label for="sidebar_color">Sidebar Color</label>
            <input type="color" class="form-control" id="sidebar_color" name="sidebar_color" value="<?php echo htmlspecialchars($admin['sidebar_color']); ?>"> <!-- Added sidebar color field -->
        </div>
        <div class="form-group">
            <label for="profile_picture">Profile Picture</label>
            <input type="file" class="form-control-file" id="profile_picture" name="profile_picture">
        </div>
        <div class="form-group">
            <label for="password">New Password (optional)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<?php include('footer.php'); ?>
