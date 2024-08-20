<?php
include 'db.php';
include 'header.php';

// Initialize variables
$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;
$question = null;
$courses_result = $conn->query("SELECT course_id, course_name FROM courses");

// Fetch question details if question_id is valid
if ($question_id) {
    $stmt = $conn->prepare("
        SELECT question_id, course_id, question_text, option1, option2, option3, option4, correct_option 
        FROM questions 
        WHERE question_id = ?
    ");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $question = $result->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>Question not found.</div>";
    }
    $stmt->close();
}

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = intval($_POST['course_id']);
    $question_text = $_POST['question_text'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];
    $correct_option = $_POST['correct_option'];
    
    $update_stmt = $conn->prepare("
        UPDATE questions 
        SET course_id = ?, question_text = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, correct_option = ? 
        WHERE question_id = ?
    ");
    $update_stmt->bind_param("issssssi", $course_id, $question_text, $option1, $option2, $option3, $option4, $correct_option, $question_id);
    
    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Question updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating question: " . $conn->error . "</div>";
    }
    $update_stmt->close();
}
?>

<div class="container mt-4">
    <h1>Edit Question</h1>
    <?php if ($question): ?>
        <form method="post">
            <div class="form-group">
                <label for="course_id">Course:</label>
                <select id="course_id" name="course_id" class="form-control" required>
                    <option value="">Select a course</option>
                    <?php while ($course_row = $courses_result->fetch_assoc()): ?>
                        <option value="<?= $course_row['course_id']; ?>" <?= $course_row['course_id'] == $question['course_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($course_row['course_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="question_text">Question:</label>
                <textarea id="question_text" name="question_text" class="form-control" rows="3" required><?= htmlspecialchars($question['question_text']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="option1">Option 1:</label>
                <input type="text" id="option1" name="option1" class="form-control" value="<?= htmlspecialchars($question['option1']); ?>" required>
            </div>
            <div class="form-group">
                <label for="option2">Option 2:</label>
                <input type="text" id="option2" name="option2" class="form-control" value="<?= htmlspecialchars($question['option2']); ?>" required>
            </div>
            <div class="form-group">
                <label for="option3">Option 3:</label>
                <input type="text" id="option3" name="option3" class="form-control" value="<?= htmlspecialchars($question['option3']); ?>" required>
            </div>
            <div class="form-group">
                <label for="option4">Option 4:</label>
                <input type="text" id="option4" name="option4" class="form-control" value="<?= htmlspecialchars($question['option4']); ?>" required>
            </div>
            <div class="form-group">
                <label for="correct_option">Correct Option:</label>
                <input type="text" id="correct_option" name="correct_option" class="form-control" value="<?= htmlspecialchars($question['correct_option']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Question</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">No question data to display.</div>
    <?php endif; ?>
    <a href="view_exam_results.php?question_id=<?php echo htmlspecialchars($question_id); ?>" class="btn btn-secondary mt-3">Back to Question</a>
</div>

<?php
include 'footer.php';
$conn->close();
?>
