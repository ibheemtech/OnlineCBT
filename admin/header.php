<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('db.php');

// Fetch the admin details from the database
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Define base URL for consistent linking
// Adjust based on your project's root URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Admin Dashboard</title>
    <style>
        .navbar {
            background-color: <?php echo htmlspecialchars($admin['navbar_color']); ?> !important;
            height: 50px;
            padding: 5px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: #fff;
            padding: 5px;
        }
        
        .navbar-brand {
            font-size: 1.2rem;
        }

        .sidebar {
            height: 100vh;
            position: fixed;
            left: -220px;
            top: 50px;
            width: 220px;
            background-color: <?php echo htmlspecialchars($admin['sidebar_color']); ?> !important;
            padding-top: 10px;
            transition: left 0.3s;
            overflow-y: auto;
            z-index: 999;
        }

        .sidebar a {
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            color: #ffffff;
            display: block;
        }

        .sidebar a:hover {
            background-color: #575d63;
        }

        .content {
            margin-left: 0;
            padding: 20px;
            margin-top: 50px;
            transition: margin-left 0.3s;
            position: relative;
            top: 50px;
        }

        .toggle-sidebar {
    cursor: pointer;
    padding: 5px 10px;
    color: #fff; /* Text/icon color */
    background-color: #007bff; /* Background color */
    border: 1px solid #0069d9; /* Border color (optional) */
    font-size: 1.2rem;
    border-radius: 4px; /* Optional: Rounded corners */
}

.toggle-sidebar:hover {
    background-color: #0056b3; /* Darker shade for hover effect */
    border-color: #004085; /* Border color on hover (optional) */
}

        .sidebar.show {
            left: 0;
        }

        .content.shifted {
            margin-left: 220px;
        }

        @media (max-width: 768px) {
            .content.shifted {
                margin-left: 0;
            }
        }

        .sidebar .collapse {
            background-color: #41484e;
        }

        .sidebar .collapse a {
            padding-left: 40px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<header class="main-header">
    <nav class="navbar navbar-expand-lg">
        <a href="dashboard.php" class="navbar-brand">Admin Panel</a>
        <button class="toggle-sidebar" id="toggleSidebar">&#9776;</button>
        <div class="ml-auto">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="uploads/<?php echo htmlspecialchars($admin['profile_picture']); ?>" class="rounded-circle" alt="Admin" width="30" height="30">
                        <?php echo htmlspecialchars($admin['name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="adminDropdown">
                        <a class="dropdown-item" href="edit_admin.php">Admin Settings</a>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="#manageCourses" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-book"></i> Manage Courses</a>
    <div id="manageCourses" class="collapse">
        <a href="courses.php" class="pl-4"><i class="fas fa-eye"></i> View Courses</a>
        <a href="add_course.php" class="pl-4"><i class="fas fa-plus"></i> Add Course</a>
    </div>
    <a href="#manageQuestions" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-question-circle"></i> Manage Questions</a>
    <div id="manageQuestions" class="collapse">
        <a href="view_questions.php" class="pl-4"><i class="fas fa-eye"></i> View Questions</a>
        <a href="add_question.php" class="pl-4"><i class="fas fa-plus"></i> Add Question</a>
    </div>
    <a href="#manageUsers" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-users"></i> Manage Users</a>
    <div id="manageUsers" class="collapse">
        <a href="view_users.php" class="pl-4"><i class="fas fa-eye"></i> View Users</a>
        <a href="add_users.php" class="pl-4"><i class="fas fa-user-plus"></i> Add User</a>
        
    </div>
    <a href="#manageResults" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-chart-bar"></i> Manage Results</a>
    <div id="manageResults" class="collapse">
        <a href="view_exam_results.php" class="pl-4"><i class="fas fa-eye"></i> View Results</a>
     
    </div>
</div>

<div class="content" id="content">

