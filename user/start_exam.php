<?php include '../includes/header.php'; ?>
<?php 
include '../includes/db.php'; // Ensure this file sets up $conn
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Check if the exam has already been submitted
// Check if the exam has already been submitted and get the submission time
$check_query = "SELECT created_at FROM exam_results WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($check_query);

if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("ii", $user_id, $course_id);

if (!$stmt->execute()) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

$check_result = $stmt->get_result();

if ($check_result === false) {
    die('Get result failed: ' . htmlspecialchars($stmt->error));
}

$row = $check_result->fetch_assoc();

if ($row) {
    $created_at = $row['created_at'];
    $formatted_date = date('Y-m-d H:i:s', strtotime($created_at)); // Format the date

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'info',
            title: 'Exam Already Submitted',
            text: 'You submitted this exam on $formatted_date.',
            confirmButtonText: 'OK'
        }).then(function() {
            window.location.href = 'dashboard.php'; // Redirect to a desired page after acknowledging
        });
    });
    </script>";
    include '../includes/footer.php';
    exit();
}
// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch course duration and timer
$course_query = "SELECT timer FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course_result = $stmt->get_result();
$course = $course_result->fetch_assoc();
$exam_duration = (int)$course['timer'] * 60; // Convert minutes to seconds

// Initialize session for timer
if (!isset($_SESSION['exam_start_time'])) {
    $_SESSION['exam_start_time'] = time();
}

// Calculate the time remaining
$elapsed_time = time() - $_SESSION['exam_start_time'];
$time_remaining = max(0, $exam_duration - $elapsed_time); // Ensure it doesn't go below 0


// Fetch questions for the course
$questions_query = "SELECT * FROM questions WHERE course_id = ? ORDER BY question_id ASC";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);

// Shuffle questions for the user if not already shuffled
if (!isset($_SESSION['shuffled_questions'])) {
    shuffle($questions);
    $_SESSION['shuffled_questions'] = $questions;
}

$questions = $_SESSION['shuffled_questions'];

// Initialize session variables for the exam if not already set
if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = array();
}
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;
}

$current_question_index = $_SESSION['current_question'];
$current_question = $questions[$current_question_index] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
    $answer = isset($_POST['answer']) ? $_POST['answer'] : null;

    // Store the answer in session with the question ID as the key
    $_SESSION['answers'][$question_id] = $answer;

    // Navigation handling
    if (isset($_POST['next'])) {
        $_SESSION['current_question'] = min($_SESSION['current_question'] + 1, count($questions) - 1);
    } elseif (isset($_POST['prev'])) {
        $_SESSION['current_question'] = max($_SESSION['current_question'] - 1, 0);
    } elseif (isset($_POST['submit'])) {
        // Handle the submission confirmation with SweetAlert
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalQuestions = " . count($questions) . ";
            const answeredQuestions = " . count(array_filter($_SESSION['answers'])) . ";
            Swal.fire({
                title: 'Confirm Submission',
                text: 'You have answered ' + answeredQuestions + ' out of ' + totalQuestions + ' questions. Do you want to submit?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit',
                cancelButtonText: 'No, go back'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('auto_submit').click();
                }
            });
        });
        </script>";
    } elseif (isset($_POST['auto_submit'])) {
        // Redirect to the summary page
        header("Location: exam_summary.php?course_id=$course_id");
        exit();
    }

    $current_question_index = $_SESSION['current_question'];
    $current_question = $questions[$current_question_index] ?? null;
}
$time_elapsed = time() - $_SESSION['exam_start_time'];
$time_remaining = max($exam_duration - $time_elapsed, 0);

$time_percentage_remaining = ($time_remaining / $exam_duration) * 100;
$timer_color = $time_percentage_remaining < 15 ? 'red' : 'green';
?>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
    }
    .container {
        display: flex;
        height: 100vh;
        overflow: hidden;
    }
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100%;
        background-color: #ffffff;
        border-right: 1px solid #ddd;
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
    }
    .sidebar img {
    width: 150px;
    height: 150px;
    margin-top: 35%; /* Adjust percentage as needed */
    margin-bottom: 20px;
}

    .sidebar h3, .sidebar h4 {
        text-align: center;
    }
    .sidebar .timer {
        margin-bottom: 20px;
        color: <?php echo $timer_color; ?>;
        font-size: 20px;
        text-align: center;
    }
    .content {
        margin-left: 270px;
        padding: 20px;
        flex: 1;
        overflow-y: auto;
        background-color: #ffffff;
        border-left: 1px solid #ddd;
    }
    .question-card {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        width: 100%;
    }
    .navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .navigation button {
        background-color: #007bff;
        border: none;
        color: white;
        padding: 10px 20px;
        margin: 5px;
        border-radius: 5px;
        cursor: pointer;
    }
    .navigation button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }
    .sidebar .question-nav a {
        display: block;
        padding: 10px;
        text-align: center;
        margin: 5px 0;
        text-decoration: none;
        color: #000;
    }
    .sidebar .question-nav a.answered {
        background-color: #d4edda;
        color: #155724;
    }
    .sidebar .question-nav a.unanswered {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>


<!-- HTML and JavaScript for rendering questions and handling navigation -->
>
    <div class="container">
    <div class="sidebar">
        <img src="../admin/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
        <h4><?php echo htmlspecialchars($user['username']); ?></h4>
        <div class="timer" id="timer">
            Time Remaining: <?php echo gmdate("i:s", $time_remaining); ?>
        </div>
        <div class="question-nav">
            <?php foreach ($questions as $index => $question): ?>
                <a href="#" class="<?php echo isset($_SESSION['answers'][$question['question_id']]) ? 'answered' : 'unanswered'; ?>" onclick="navigateToQuestion(<?php echo $index; ?>)">
                    <?php echo ($index + 1); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="content">
        <div class="question-card">
            <?php if ($current_question): ?>
            <form action="" method="post">
                <input type="hidden" name="question_id" value="<?php echo $current_question['question_id']; ?>">
                <h4><?php echo "Question " . ($current_question_index + 1) . ": " . htmlspecialchars($current_question['question_text']); ?></h4>
                <!-- Displaying the options and retaining the selected one -->
                <div class="form-check">
                    <input type="radio" id="option1_<?php echo $current_question['question_id']; ?>" name="answer" value="1" class="form-check-input" <?php echo isset($_SESSION['answers'][$current_question['question_id']]) && $_SESSION['answers'][$current_question['question_id']] == '1' ? 'checked' : ''; ?>>
                    <label for="option1_<?php echo $current_question['question_id']; ?>" class="form-check-label"><?php echo htmlspecialchars($current_question['option1']); ?></label>
                </div>
                <div class="form-check">
                    <input type="radio" id="option2_<?php echo $current_question['question_id']; ?>" name="answer" value="2" class="form-check-input" <?php echo isset($_SESSION['answers'][$current_question['question_id']]) && $_SESSION['answers'][$current_question['question_id']] == '2' ? 'checked' : ''; ?>>
                    <label for="option2_<?php echo $current_question['question_id']; ?>" class="form-check-label"><?php echo htmlspecialchars($current_question['option2']); ?></label>
                </div>
                <div class="form-check">
                    <input type="radio" id="option3_<?php echo $current_question['question_id']; ?>" name="answer" value="3" class="form-check-input" <?php echo isset($_SESSION['answers'][$current_question['question_id']]) && $_SESSION['answers'][$current_question['question_id']] == '3' ? 'checked' : ''; ?>>
                    <label for="option3_<?php echo $current_question['question_id']; ?>" class="form-check-label"><?php echo htmlspecialchars($current_question['option3']); ?></label>
                </div>
                <div class="form-check">
                    <input type="radio" id="option4_<?php echo $current_question['question_id']; ?>" name="answer" value="4" class="form-check-input" <?php echo isset($_SESSION['answers'][$current_question['question_id']]) && $_SESSION['answers'][$current_question['question_id']] == '4' ? 'checked' : ''; ?>>
                    <label for="option4_<?php echo $current_question['question_id']; ?>" class="form-check-label"><?php echo htmlspecialchars($current_question['option4']); ?></label>
                </div>
                
            <br>
                <!-- Add more options as needed -->

                <div class="navigation">
                    <button type="submit" name="prev" <?php echo $current_question_index == 0 ? 'disabled' : ''; ?>>Previous</button>
                    <button type="submit" name="next" <?php echo $current_question_index == count($questions) - 1 ? 'disabled' : ''; ?>>Next</button>
                    <button type="submit" name="submit">Submit</button>
                    <button type="submit" name="auto_submit" id="auto_submit" style="display:none;"></button>
                </div>
            </form>
            <?php else: ?>
                <p>No questions available for this exam.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function startTimer(duration, display) {
        var timer = duration, minutes, seconds;
        var interval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = "Time Remaining: " + minutes + ":" + seconds;

            if (--timer < 0) {
                clearInterval(interval);
                display.textContent = "Time's up!";
                
                // Automatically submit the exam
                document.getElementById('auto_submit').click();
            }
        }, 1000);
    }

    window.onload = function () {
        var timeRemaining = <?php echo $time_remaining; ?>;
        var display = document.querySelector('#timer');
        startTimer(timeRemaining, display);
    };
function navigateToQuestion(index) {
    // JavaScript function to handle sidebar navigation clicks
    document.querySelector('[name="submit"]').disabled = true;
    document.querySelector('[name="auto_submit"]').style.display = 'none';
    document.querySelector('[name="prev"]').disabled = index === 0;
    document.querySelector('[name="next"]').disabled = index === <?php echo count($questions) - 1; ?>;
    document.getElementById('auto_submit').style.display = 'none';
    document.querySelector('[name="current_question"]').value = index;
    document.querySelector('form').submit();
}
</script>
