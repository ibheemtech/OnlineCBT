<?php
// Include necessary files and start session
include '../includes/header.php'; 
include '../includes/db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Query to fetch courses the user has results for
$query = "SELECT DISTINCT c.course_id, c.course_name
          FROM courses c
          JOIN course_results cr ON c.course_id = cr.course_id
          WHERE cr.user_id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    die("Error fetching courses: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Courses</title>
    <link rel="stylesheet" href="path/to/bootstrap.css"> <!-- Include Bootstrap CSS -->
</head>
<body>
    <div class="container mt-5">
        <h1>Available Courses</h1>
        <?php if ($result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($row['course_name']); ?>
                        <form action="view_results.php" method="post" class="mb-0">
                            <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                            <button type="submit" class="btn btn-primary">View Results</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-warning">No courses available for your results.</div>
        <?php endif; ?>
    </div>
</body>
</html>
