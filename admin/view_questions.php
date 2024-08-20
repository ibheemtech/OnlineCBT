<?php
include 'db.php';
include 'header.php';

// Fetch courses for selection
$courses_result = $conn->query("SELECT course_id, course_name FROM courses");
if (!$courses_result) {
    die("Error fetching courses: " . $conn->error);
}

// Initialize variables
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : (isset($_GET['course_id']) ? intval($_GET['course_id']) : 0);
$search_query = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : (isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "");
$course = null;
$questions = [];

// Pagination and entries per page setup
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entries_per_page;

// Check if a course is selected
if ($course_id) {
    // Fetch selected course details and questions
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

        // Fetch questions associated with the selected course with pagination and search
        $question_sql = "
            SELECT question_id, question_text, option1, option2, option3, option4, correct_option 
            FROM questions 
            WHERE course_id = ? AND question_text LIKE ?
            LIMIT ? OFFSET ?
        ";
        $question_stmt = $conn->prepare($question_sql);
        $like_search_query = "%" . $search_query . "%";
        $question_stmt->bind_param("isii", $course_id, $like_search_query, $entries_per_page, $offset);
        $question_stmt->execute();
        $question_result = $question_stmt->get_result();
        
        while ($question_row = $question_result->fetch_assoc()) {
            $questions[] = $question_row;
        }

        // Get total number of questions for pagination
        $total_questions_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM questions WHERE course_id = ? AND question_text LIKE ?");
        $total_questions_stmt->bind_param("is", $course_id, $like_search_query);
        $total_questions_stmt->execute();
        $total_questions_result = $total_questions_stmt->get_result();
        $total_questions = $total_questions_result->fetch_assoc()['total'];
        $total_pages = ceil($total_questions / $entries_per_page);

        $question_stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Course not found.</div>";
    }
    $course_stmt->close();
}

// Handle deletion of a question
if (isset($_GET['delete_question_id'])) {
    $delete_question_id = intval($_GET['delete_question_id']);
    $delete_stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
    $delete_stmt->bind_param("i", $delete_question_id);
    if ($delete_stmt->execute()) {
        echo "<div class='alert alert-success'>Question deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting question: " . $conn->error . "</div>";
    }
    $delete_stmt->close();
    // Refresh the page to update the question list
    header("Location: view_question.php?course_id=" . $course_id);
    exit;
}
?>

<div class="container mt-4">
    <h1>View Questions</h1>
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
            <h2>Questions</h2>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <label for="entries" class="form-label">Show</label>
                    <select id="entries" class="form-select d-inline-block w-auto" onchange="location = this.value;">
                        <option value="?course_id=<?= $course_id ?>&entries=10" <?= $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="?course_id=<?= $course_id ?>&entries=25" <?= $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="?course_id=<?= $course_id ?>&entries=50" <?= $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="?course_id=<?= $course_id ?>&entries=100" <?= $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                    <span>entries</span>
                </div>
                <form method="get" class="d-flex align-items-center">
                    <input type="hidden" name="course_id" value="<?= $course_id; ?>">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?= htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <?php if (count($questions) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Question</th>
                            <th>Option 1</th>
                            <th>Option 2</th>
                            <th>Option 3</th>
                            <th>Option 4</th>
                            <th>Correct Option</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $index => $question): ?>
                            <tr>
                                <td><?= $offset + $index + 1; ?></td>
                                <td><?= htmlspecialchars($question['question_text']); ?></td>
                                <td><?= htmlspecialchars($question['option1']); ?></td>
                                <td><?= htmlspecialchars($question['option2']); ?></td>
                                <td><?= htmlspecialchars($question['option3']); ?></td>
                                <td><?= htmlspecialchars($question['option4']); ?></td>
                                <td><?= htmlspecialchars($question['correct_option']); ?></td>
                                <td>
                                    <a href="edit_question.php?question_id=<?= $question['question_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?course_id=<?= $course_id; ?>&delete_question_id=<?= $question['question_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">No questions found for this course.</div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span>Showing <?= ($offset + 1); ?> to <?= min($offset + $entries_per_page, $total_questions); ?> of <?= $total_questions; ?> entries</span>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?course_id=<?= $course_id; ?>&search=<?= urlencode($search_query); ?>&entries=<?= $entries_per_page; ?>&page=<?= ($page - 1); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?course_id=<?= $course_id; ?>&search=<?= urlencode($search_query); ?>&entries=<?= $entries_per_page; ?>&page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?course_id=<?= $course_id; ?>&search=<?= urlencode($search_query); ?>&entries=<?= $entries_per_page; ?>&page=<?= ($page + 1); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
    <br>
    <a href="add_question.php" class="btn btn-primary">Add New Question</a>
</div>
</div>

<?php include 'footer.php'; ?>
