<?php
include 'db.php';
include 'header.php'; // Include your header file

// Get the course_id and user_id from the URL parameters
$course_id = $_GET['course_id'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$course_id || !$user_id) {
    die("Course ID and User ID are required.");
}

// Fetch exam details
$detailsQuery = "SELECT q.question_text, q.correct_option, er.answer AS user_answer,
                        q.option1, q.option2, q.option3, q.option4
                 FROM exam_results er
                 JOIN questions q ON er.question_id = q.question_id
                 WHERE er.course_id = ? AND er.user_id = ?";
$stmt = $conn->prepare($detailsQuery);
if ($stmt === false) {
    die("Error preparing the SQL statement: " . $conn->error);
}
$stmt->bind_param("ii", $course_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No exam details found for the selected course and user.");
}

// Fetch the username
$userQuery = "SELECT username FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$username = $userResult->fetch_assoc()['username'];
?>

<div class="container mt-5">
    <h2 class="mb-4">Exam Details for <?php echo htmlspecialchars($username); ?></h2>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="mb-4 p-3 border rounded">
            <h5><?php echo htmlspecialchars($row['question_text']); ?></h5>
            <p>
                <strong>Your Answer:</strong> 
                <button class="btn <?php echo $row['user_answer'] == $row['correct_option'] ? 'btn-success' : 'btn-danger'; ?>" disabled>
                    <?php echo htmlspecialchars($row['user_answer'] == 1 ? $row['option1'] : ($row['user_answer'] == 2 ? $row['option2'] : ($row['user_answer'] == 3 ? $row['option3'] : $row['option4']))); ?>
                </button>
            </p>
            <?php if ($row['user_answer'] != $row['correct_option']): ?>
                <p>
                    <strong>Correct Answer:</strong> 
                    <button class="btn btn-success" disabled>
                        <?php echo htmlspecialchars($row['correct_option'] == 1 ? $row['option1'] : ($row['correct_option'] == 2 ? $row['option2'] : ($row['correct_option'] == 3 ? $row['option3'] : $row['option4']))); ?>
                    </button>
                </p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <div class="mt-4">
        <a href="view_exam_results.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Back</a>
    </div>
</div>

<?php include 'footer.php'; ?>
