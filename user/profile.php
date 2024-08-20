<?php
include '../config/db.php';
include '../includes/functions.php';
include '../includes/auth.php';

if (!isUser()) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$test_id = $_GET['test_id'];

$sql = "SELECT * FROM questions WHERE test_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $test_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score = 0;
    foreach ($_POST['answers'] as $question_id => $selected_option) {
        $sql = "SELECT is_correct FROM options WHERE question_id = ? AND option_text = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $question_id, $selected_option);
        $stmt->execute();
        $option_result = $stmt->get_result();
        $option = $option_result->fetch_assoc();
        if ($option['is_correct']) {
            $score++;
        }
    }

    $sql = "INSERT INTO results (user_id, test_id, score) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $user_id, $test_id, $score);
    $stmt->execute();

    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Exam Submitted',
            text: 'Your score is $score.'
        }).then(() => {
            window.location.href = 'results.php';
        });
        </script>";
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<h1>Take Exam</h1>

<form method="post">
    <?php while ($question = $result->fetch_assoc()): ?>
        <div>
            <p><?php echo $question['question_text']; ?></p>
            <?php
            $sql = "SELECT * FROM options WHERE question_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $question['id']);
            $stmt->execute();
            $options = $stmt->get_result();
            while ($option = $options->fetch_assoc()):
            ?>
                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $option['option_text']; ?>" required> <?php echo $option['option_text']; ?><br>
            <?php endwhile; ?>
        </div>
    <?php endwhile; ?>
    <button type="submit">Submit</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php include '../includes/footer.php'; ?>
