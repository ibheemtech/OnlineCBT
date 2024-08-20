<?php

include 'header.php';

$success_message = '';

if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $course_name = $_POST['course_name'];
        $timer = $_POST['timer'];
        $total_questions = $_POST['total_questions'];

        $stmt = $conn->prepare("UPDATE courses SET course_name = ?, timer = ?, total_questions = ? WHERE course_id = ?");
        $stmt->bind_param("siii", $course_name, $timer, $total_questions, $course_id);
        if ($stmt->execute()) {
            $success_message = 'Course updated successfully!';
        } else {
            $success_message = 'Failed to update course.';
        }
        $stmt->close();
    }
} else {
    $success_message = 'Invalid course ID.';
}
?>

<div class="container mt-4">
    <h1>Edit Course</h1>
    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="course_name">Course Name:</label>
            <input type="text" class="form-control" id="course_name" name="course_name" value="<?= htmlspecialchars($course['course_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="timer">Timer (minutes):</label>
            <input type="number" class="form-control" id="timer" name="timer" value="<?= htmlspecialchars($course['timer']); ?>" required>
        </div>
        <div class="form-group">
            <label for="total_questions">Total Questions:</label>
            <input type="number" class="form-control" id="total_questions" name="total_questions" value="<?= htmlspecialchars($course['total_questions']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Course</button>
    </form>
    <br>
    <a href="view_exam_results.php?course_id=<?php echo htmlspecialchars($course_id); ?>" class="btn btn-secondary mt-3">Back to Course</a>

<?php
include 'footer.php';
$conn->close();
?>
