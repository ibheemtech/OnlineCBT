<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include('db.php');

// Fetch the user details from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch available courses from the database
$courses_query = "SELECT * FROM courses";
$courses_result = $conn->query($courses_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>User Dashboard</title>
    <style>
        .navbar {
            background-color: #000080;
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
            background-color: #00FFFF;
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
            color: #fff;
            background-color: #007bff;
            border: 1px solid #0069d9;
            font-size: 1.2rem;
            border-radius: 4px;
        }

        .toggle-sidebar:hover {
            background-color: #0056b3;
            border-color: #004085;
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

        .text-muted {
            color: #6c757d !important;
        }
    </style>
</head>
<body>
<header class="main-header">
    <nav class="navbar navbar-expand-lg">
        <a href="dashboard.php" class="navbar-brand">User Dashboard</a>
        <button class="toggle-sidebar" id="toggleSidebar">&#9776;</button>
        <div class="ml-auto">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="edit_user.php">User Settings</a>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>


    <!-- Content for the main dashboard goes here -->
