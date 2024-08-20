<?php

include('../includes/header.php');

// Get the course_id from the URL
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Fetch course details from the database
$course_query = "SELECT * FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($course_query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course_result = $stmt->get_result();
$course = $course_result->fetch_assoc();

if (!$course) {
    echo "Course not found.";
    exit();
}

// Fetch the total number of questions for the course
$question_query = "SELECT COUNT(*) as total_questions FROM questions WHERE course_id = ?";
$stmt = $conn->prepare($question_query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $course_id);
$stmt->execute();
$question_result = $stmt->get_result();
$question_count = $question_result->fetch_assoc();
$total_questions = isset($question_count['total_questions']) ? $question_count['total_questions'] : 0;

// Include header

?>



<style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
            max-width: 600px; /* Center the content and limit the width */
        }

        .course-details {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .course-details h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }

        .course-details p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: #666;
        }

        .btn-custom {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 1rem;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>

    <div class="container">
        <div class="course-details">
            <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
            <p><strong>Timer:</strong> <?php echo htmlspecialchars($course['timer']); ?> minutes</p>
            <p><strong>Total Questions:</strong> <?php echo $total_questions; ?></p>
            <a href="start_exam.php?course_id=<?php echo $course_id; ?>" class="btn btn-custom">Start Exam</a>
            <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
        </div>
    </div>
    
<?php include '../includes/footer.php'; ?>
