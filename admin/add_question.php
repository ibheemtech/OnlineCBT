<?php
include('header.php');

// Fetch active courses for selection
$courses_result = $conn->query("SELECT course_id, course_name FROM courses");
if (!$courses_result) {
    die("Error fetching courses: " . $conn->error);
}

// Initialize variables
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$course = null;

// Check if a course is selected
if ($course_id) {
    // Fetch selected course details
    $course_stmt = $conn->prepare("
        SELECT course_name, timer, total_questions, is_active,
            (SELECT COUNT(*) FROM questions WHERE course_id = ?) AS current_questions 
        FROM courses 
        WHERE course_id = ?
    ");
    $course_stmt->bind_param("ii", $course_id, $course_id);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    
    if ($course_result->num_rows > 0) {
        $course = $course_result->fetch_assoc();
        
        // Handle adding a new question
        if (isset($_POST['question_text'])) {
            $question_text = $_POST['question_text'];
            $option1 = $_POST['option1'];
            $option2 = $_POST['option2'];
            $option3 = $_POST['option3'];
            $option4 = $_POST['option4'];
            $correct_option = $_POST['correct_option'];
            $max_questions = $course['total_questions'];
            $current_questions = $course['current_questions'];

            if ($current_questions < $max_questions) {
                // Insert the question
                $question_stmt = $conn->prepare("
                    INSERT INTO questions (course_id, question_text, option1, option2, option3, option4, correct_option) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $question_stmt->bind_param("issssss", $course_id, $question_text, $option1, $option2, $option3, $option4, $correct_option);
                if ($question_stmt->execute()) {
                    echo "<div class='alert alert-success'>Question added successfully!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error adding question: " . $conn->error . "</div>";
                }
                $question_stmt->close();
            } else {
                echo "<div class='alert alert-warning'>Cannot add more questions. Limit reached for this course.</div>";
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Course not found or is not available.</div>";
    }
    $course_stmt->close();
}
?>

<div class="container mt-4">
    <h1>Add Questions</h1>
    <form method="post">
        <div class="form-group">
            <label for="course_id">Select Course:</label>
            <select id="course_id" name="course_id" class="form-control" required>
                <option value="">Select a course</option>
                <?php while ($course_row = $courses_result->fetch_assoc()): ?>
                    <option value="<?= $course_row['course_id']; ?>" <?= $course_id == $course_row['course_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($course_row['course_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Show Course Details</button>
    </form>

    <?php if ($course): ?>
        <div class="mt-4">
            <h2>Course Details</h2>
            <p><strong>Course Name:</strong> <?= htmlspecialchars($course['course_name']); ?></p>
            <p><strong>Timer (minutes):</strong> <?= htmlspecialchars($course['timer']); ?></p>
            <p><strong>Total Questions Allowed:</strong> <?= htmlspecialchars($course['total_questions']); ?></p>
            <p><strong>Current Number of Questions:</strong> <?= htmlspecialchars($course['current_questions']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge <?= $course['is_active'] ? 'badge-primary' : 'badge-danger'; ?>">
                    <?= $course['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
                <?php if (!$course['is_active']): ?>
                    <span class="text-danger">(This course is currently inactive)</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="mt-4">
            <h2>Add Question</h2>
            <form method="post">
                <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id); ?>">
                <div class="form-group">
                    <label for="question_text">Question:</label>
                    <textarea id="question_text" name="question_text" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="option1">Option 1:</label>
                    <input type="text" id="option1" name="option1" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="option2">Option 2:</label>
                    <input type="text" id="option2" name="option2" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="option3">Option 3:</label>
                    <input type="text" id="option3" name="option3" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="option4">Option 4:</label>
                    <input type="text" id="option4" name="option4" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Correct Option:</label><br>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="correct_option" value="1" required>
                        <label class="form-check-label">Option 1</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="correct_option" value="2" required>
                        <label class="form-check-label">Option 2</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="correct_option" value="3" required>
                        <label class="form-check-label">Option 3</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="correct_option" value="4" required>
                        <label class="form-check-label">Option 4</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Add Question</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
include 'footer.php';
$conn->close();
?>
