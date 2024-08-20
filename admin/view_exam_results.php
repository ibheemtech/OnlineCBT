<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
include 'header.php'; // Include your header file

// Step 1: Fetch all courses from the database
$coursesResult = $conn->query("SELECT course_id, course_name FROM courses");
if (!$coursesResult) {
    die("Error fetching courses: " . $conn->error);
}

// Step 2: Handle the form submission
$course_id = null;
$search_query = isset($_GET['search']) ? $_GET['search'] : ''; // Get search query from URL
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10; // Default to 10 entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Default to page 1

// Calculate offset and total records
$offset = ($page - 1) * $entries_per_page;
$total_records = 0; // Initialize

$results = []; // Initialize an empty array to hold results

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    // Fetch exam results
    $resultsQuery = "SELECT u.id as user_id, u.username, er.question_id, er.answer AS user_answer, q.correct_option AS correct_answer
                     FROM exam_results er
                     JOIN questions q ON er.question_id = q.question_id
                     JOIN users u ON er.user_id = u.id
                     WHERE er.course_id = ?";

    if (!empty($search_query)) {
        // Add search filter if search query is provided
        $resultsQuery .= " AND (u.username LIKE ?)";
    }

    $stmt = $conn->prepare($resultsQuery);
    if ($stmt === false) {
        die("Error preparing the SQL statement: " . $conn->error);
    }

    if (!empty($search_query)) {
        // Bind search parameter
        $search_query = "%$search_query%";
        $stmt->bind_param("is", $course_id, $search_query);
    } else {
        $stmt->bind_param("i", $course_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    // Process results
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $username = $row['username'];
        $user_id = $row['user_id']; // Store the user ID for the links

        if (!isset($results[$username])) {
            $results[$username] = [
                'user_id' => $user_id,
                'total_questions' => 0,
                'correct_answers' => 0,
                'incorrect_answers' => 0
            ];
        }
        $results[$username]['total_questions']++;
        if ($row['user_answer'] == $row['correct_answer']) {
            $results[$username]['correct_answers']++;
        } else {
            $results[$username]['incorrect_answers']++;
        }
    }

    // Set total records
    $total_records = count($results); // Number of results found
}

// Calculate total pages
$total_pages = ceil($total_records / $entries_per_page);
?>

<div class="container mt-5">
    <h2 class="mb-4">View Exam Results</h2>

    <!-- Course Selection Form -->
    <form action="" method="post">
        <div class="form-group">
            <label for="course_id">Select Course:</label>
            <select id="course_id" name="course_id" class="form-control" required>
                <option value="">Select a course</option>
                <?php while ($course = $coursesResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php echo ($course_id == $course['course_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">View Results</button>
    </form>

    <!-- Search and Entries Selection -->
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
            <input type="hidden" name="entries" value="<?= $entries_per_page; ?>">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?= htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Display Entries Range -->
    
    <!-- Display Results -->
    <?php if (!empty($results)): ?>
        <div class="mt-5">
            <h3>Exam Results</h3>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Total Questions</th>
                        <th>Correct Answers</th>
                        <th>Incorrect Answers</th>
                        <th>Actions</th> <!-- New column for action buttons -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $username => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($username); ?></td>
                            <td><?php echo $data['total_questions']; ?></td>
                            <td><?php echo $data['correct_answers']; ?></td>
                            <td><?php echo $data['incorrect_answers']; ?></td>
                            <td>
                                <div class="mt-4">
                                    <a href="view_exam_details.php?course_id=<?php echo htmlspecialchars($course_id); ?>&user_id=<?php echo htmlspecialchars($data['user_id']); ?>" class="btn btn-secondary">View Exam Details</a>
                                    <a href="upload_results.php?course_id=<?php echo htmlspecialchars($course_id); ?>&user_id=<?php echo htmlspecialchars($data['user_id']); ?>" class="btn btn-primary">Upload Results</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-4">No results found for the search query "<?php echo htmlspecialchars($search_query); ?>".</div>
    <?php endif; ?>
    <div>
        <span>Showing <?= ($offset + 1); ?> to <?= min($offset + $entries_per_page, $total_records); ?> of <?= $total_records; ?> entries</span>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
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

<?php include 'footer.php'; ?>
<?php
// Close the database connection
$conn->close();
?>
