<?php
include 'db.php';
include 'header.php'; // Include your header file

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
$percentage = isset($_POST['percentage']) ? (float)$_POST['percentage'] : null;
$displayResults = isset($_POST['display_results']);
$submitResults = isset($_POST['submit_results']);
$submitAllResults = isset($_POST['submit_all_results']);
$submitSingleResults = isset($_POST['submit_single_results']);
$percentages = [];

if ($submitResults || $submitAllResults || $submitSingleResults) {
    // Validate percentage
    if ($percentage === null || $percentage < 0 || $percentage > 100) {
        echo "<div class='container mt-5'>
                <div class='alert alert-danger'>Percentage must be between 0 and 100.</div>
                <a href='upload_results.php?course_id=" . htmlspecialchars($course_id) . "' class='btn btn-secondary'>Back</a>
              </div>";
        include 'footer.php';
        exit();
    }

    if ($submitAllResults) {
        // Save all results to the database
        $fetchResultsQuery = "SELECT user_id, COUNT(*) AS total_questions,
                              SUM(CASE WHEN answer = correct_option THEN 1 ELSE 0 END) AS correct_answers
                              FROM exam_results er
                              JOIN questions q ON er.question_id = q.question_id
                              WHERE er.course_id = ?
                              GROUP BY user_id";
        $fetchResultsStmt = $conn->prepare($fetchResultsQuery);
        $fetchResultsStmt->bind_param("i", $course_id);
        $fetchResultsStmt->execute();
        $results = $fetchResultsStmt->get_result();

        $updateResultsQuery = "INSERT INTO course_results (course_id, user_id, total_questions, correct_answers, percentage)
                               VALUES (?, ?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE total_questions = VALUES(total_questions), 
                                                       correct_answers = VALUES(correct_answers), 
                                                       percentage = VALUES(percentage)";
        $updateResultsStmt = $conn->prepare($updateResultsQuery);

        while ($row = $results->fetch_assoc()) {
            $user_id = $row['user_id'];
            $total_questions = $row['total_questions'];
            $correct_answers = $row['correct_answers'];

            // Prevent division by zero
            $calculatedPercentage = ($total_questions > 0) ? ($correct_answers / $total_questions) * $percentage : 0;

            // Bind parameters and execute
            $updateResultsStmt->bind_param("iiiid", $course_id, $user_id, $total_questions, $correct_answers, $calculatedPercentage);
            if (!$updateResultsStmt->execute()) {
                echo "<div class='container mt-5'>
                        <div class='alert alert-danger'>Error updating results: " . htmlspecialchars($updateResultsStmt->error) . "</div>
                        <a href='upload_results.php?course_id=" . htmlspecialchars($course_id) . "' class='btn btn-secondary'>Back</a>
                      </div>";
                include 'footer.php';
                exit();
            }
        }

        echo "<div class='container mt-5'>
                <div class='alert alert-success'>All results have been successfully updated with the percentage!</div>
                <a href='view_exam_results.php?course_id=" . htmlspecialchars($course_id) . "' class='btn btn-secondary'>Back to Results</a>
              </div>";
        include 'footer.php';
        exit();
    }

    if ($submitSingleResults) {
        // Validate checkbox submission
        if (!isset($_POST['selected_users']) || !is_array($_POST['selected_users'])) {
            echo "<div class='container mt-5'>
                    <div class='alert alert-danger'>No users selected for updating.</div>
                    <a href='upload_results.php?course_id=" . htmlspecialchars($course_id) . "' class='btn btn-secondary'>Back</a>
                  </div>";
            include 'footer.php';
            exit();
        }

        $selectedUsers = $_POST['selected_users'];

        // Update only selected results to the database
        $updateResultsQuery = "INSERT INTO course_results (course_id, user_id, total_questions, correct_answers, percentage)
                               VALUES (?, ?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE total_questions = VALUES(total_questions), 
                                                       correct_answers = VALUES(correct_answers), 
                                                       percentage = VALUES(percentage)";
        $updateResultsStmt = $conn->prepare($updateResultsQuery);

        foreach ($selectedUsers as $user_id) {
            // Fetch result for the specific user
            $fetchResultQuery = "SELECT user_id, COUNT(*) AS total_questions,
                                 SUM(CASE WHEN answer = correct_option THEN 1 ELSE 0 END) AS correct_answers
                                 FROM exam_results
                                 WHERE course_id = ? AND user_id = ?
                                 GROUP BY user_id";
            $fetchResultStmt = $conn->prepare($fetchResultQuery);
            $fetchResultStmt->bind_param("ii", $course_id, $user_id);
            $fetchResultStmt->execute();
            $result = $fetchResultStmt->get_result()->fetch_assoc();

            if ($result) {
                $total_questions = $result['total_questions'];
                $correct_answers = $result['correct_answers'];

                // Prevent division by zero
                $calculatedPercentage = ($total_questions > 0) ? ($correct_answers / $total_questions) * $percentage : 0;

                // Bind parameters and execute
                $updateResultsStmt->bind_param("iiiid", $course_id, $user_id, $total_questions, $correct_answers, $calculatedPercentage);
                if (!$updateResultsStmt->execute()) {
                    echo "<div class='container mt-5'>
                            <div class='alert alert-danger'>Error updating result for user $user_id: " . htmlspecialchars($updateResultsStmt->error) . "</div>
                            <a href='upload_results.php?course_id=" . htmlspecialchars($course_id) . "' class='btn btn-secondary'>Back</a>
                          </div>";
                    include 'footer.php';
                    exit();
                }
            }
        }

        echo "<div class='container mt-5'>
                <div class='alert alert-success'>Selected results have been successfully updated with the percentage!</div>
                <a href='view_exam_results.php?course_id=" . htmlspecialchars($course_id) . "' class='btn btn-secondary'>Back to Results</a>
              </div>";
        include 'footer.php';
        exit();
    }
} elseif ($displayResults && $percentage !== null) {
    // Validate percentage
    if ($percentage < 0 || $percentage > 100) {
        echo "<div class='container mt-5'>
                <div class='alert alert-danger'>Percentage must be between 0 and 100.</div>
                <a href='upload_results.php?course_id=" . htmlspecialchars($course_id) . "' class='btn btn-secondary'>Back</a>
              </div>";
        include 'footer.php';
        exit();
    }

    // Calculate percentages for display
    $fetchResultsQuery = "SELECT u.username, er.user_id, COUNT(*) AS total_questions,
                          SUM(CASE WHEN answer = correct_option THEN 1 ELSE 0 END) AS correct_answers
                          FROM exam_results er
                          JOIN questions q ON er.question_id = q.question_id
                          JOIN users u ON er.user_id = u.id
                          WHERE er.course_id = ?
                          GROUP BY u.username, er.user_id";
    $fetchResultsStmt = $conn->prepare($fetchResultsQuery);
    $fetchResultsStmt->bind_param("i", $course_id);
    $fetchResultsStmt->execute();
    $results = $fetchResultsStmt->get_result();

    while ($row = $results->fetch_assoc()) {
        $user_id = $row['user_id'];
        $total_questions = $row['total_questions'];
        $correct_answers = $row['correct_answers'];
        $calculatedPercentage = ($total_questions > 0) ? ($correct_answers / $total_questions) * $percentage : 0;
        $percentages[$user_id] = [
            'username' => $row['username'],
            'total_questions' => $total_questions,
            'correct_answers' => $correct_answers,
            'calculated_percentage' => $calculatedPercentage,
        ];
    }
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Upload Results for Course ID <?php echo htmlspecialchars($course_id); ?></h2>
    
    <!-- Step 1: Enter Percentage and Display Button -->
    <form action="" method="post">
        <div class="form-group">
            <label for="percentage">Enter Percentage for the Course:</label>
            <input type="number" step="0.01" min="0" max="100" name="percentage" id="percentage" class="form-control" required>
        </div>
        <button type="submit" name="display_results" class="btn btn-primary mt-3">Display Results</button>
    </form>

    <?php if (isset($percentages) && !empty($percentages)): ?>
        <!-- Step 2: Display Results -->
        <form action="" method="post">
            <input type="hidden" name="percentage" value="<?php echo htmlspecialchars($percentage); ?>">
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Username</th>
                        <th>Total Questions</th>
                        <th>Correct Answers</th>
                        <th>Calculated Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($percentages as $user_id => $data): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_users[]" value="<?php echo htmlspecialchars($user_id); ?>"></td>
                            <td><?php echo htmlspecialchars($data['username']); ?></td>
                            <td><?php echo htmlspecialchars($data['total_questions']); ?></td>
                            <td><?php echo htmlspecialchars($data['correct_answers']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($data['calculated_percentage'], 2)); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" name="submit_all_results" class="btn btn-success mt-3">Submit All Results to Database</button>
            <button type="submit" name="submit_single_results" class="btn btn-warning mt-3">Submit Selected Results to Database</button>
        </form>
    <?php endif; ?>

    <a href="view_exam_results.php?course_id=<?php echo htmlspecialchars($course_id); ?>" class="btn btn-secondary mt-3">Back to Results</a>
</div>

<?php include 'footer.php'; ?>
