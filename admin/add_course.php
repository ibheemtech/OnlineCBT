<?php
include('header.php');

$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = $_POST['course_name'];
    $timer = $_POST['timer'];
    $total_questions = $_POST['total_questions'];

    $stmt = $conn->prepare("INSERT INTO courses (course_name, timer, total_questions) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $course_name, $timer, $total_questions);
    if ($stmt->execute()) {
        $success_message = 'Course added successfully!';
    } else {
        $success_message = 'Failed to add course.';
    }
    $stmt->close();
}
?>

<div class="container mt-4">
    <h1>Add New Course</h1>
    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="course_name">Course Name:</label>
            <input type="text" class="form-control" id="course_name" name="course_name" required>
        </div>
        <div class="form-group">
            <label for="timer">Timer (minutes):</label>
            <input type="number" class="form-control" id="timer" name="timer" required>
        </div>
        <div class="form-group">
            <label for="total_questions">Total Questions:</label>
            <input type="number" class="form-control" id="total_questions" name="total_questions" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Course</button>
    </form>
    <br>
    <a class="btn btn-secondary" href="courses.php">Back to Courses</a>
</div>

<!-- Include Footer -->
<?php
include('footer.php');
$conn->close();
?>
