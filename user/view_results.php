<?php
// Include necessary files and start session
include '../includes/db.php';
require('../fpdf/fpdf.php');
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null;

if (!$course_id) {
    die("Invalid course ID.");
}

// Query to fetch course results
$query = "SELECT c.course_name, cr.percentage, u.username, u.name AS full_name, u.profile_picture,
                 (SELECT COUNT(*) 
                  FROM questions q 
                  WHERE q.course_id = c.course_id) AS total_questions,
                 (SELECT COUNT(*) 
                  FROM exam_results er
                  JOIN questions q ON er.question_id = q.question_id
                  WHERE er.user_id = u.id 
                    AND er.course_id = c.course_id
                    AND er.answer = q.correct_option) AS correct_answers,
                 (SELECT COUNT(*) 
                  FROM exam_results er
                  WHERE er.user_id = u.id 
                    AND er.course_id = c.course_id) AS attempted_questions
          FROM courses c 
          JOIN course_results cr ON c.course_id = cr.course_id
          JOIN users u ON cr.user_id = u.id
          WHERE cr.user_id = ? AND c.course_id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("No results found for this course.");
    }

    $course_data = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Failed to fetch results: " . $conn->error);
}

// Generate the PDF using FPDF
class PDF extends FPDF {
    // Header
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Course Exam Results', 0, 1, 'C');
        $this->Ln(10);
    }

    // Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Function to display profile picture
    function ProfilePicture($file) {
        $filePath = '../admin/uploads/' . $file;
        if ($file && file_exists($filePath)) {
            $this->Image($filePath, 10, 30, 40, 40); // Adjust the position and size as needed
            $this->Ln(50); // Move cursor down after the image
        }
    }
}

// Create PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Add profile picture
if (!empty($course_data['profile_picture'])) {
    $pdf->ProfilePicture($course_data['profile_picture']);
}

// Add user and course details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Username: ' . $course_data['username'], 0, 1);
$pdf->Cell(0, 10, 'Full Name: ' . $course_data['full_name'], 0, 1);
$pdf->Cell(0, 10, 'Course Name: ' . $course_data['course_name'], 0, 1);
$pdf->Cell(0, 10, 'Percentage: ' . $course_data['percentage'] . '%', 0, 1);
$pdf->Ln(10); // Line break

// Add results statistics
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Results Statistics', 0, 1);
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(0, 10, 'Total Questions: ' . $course_data['total_questions'], 0, 1);
$pdf->Cell(0, 10, 'Attempted Questions: ' . $course_data['attempted_questions'], 0, 1);
$pdf->Cell(0, 10, 'Incorrect Answers: ' . ($course_data['attempted_questions'] - $course_data['correct_answers']), 0, 1);
$pdf->Cell(0, 10, 'Correct Answers: ' . $course_data['correct_answers'], 0, 1);
// Output the PDF to the browser
$pdf->Output('D', 'course_results.pdf');
?>
