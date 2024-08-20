<?php
include '../includes/header.php'; // Include header if necessary
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Complete</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 100px;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Exam Completed!</h1>
        <p>Your exam has been successfully submitted.</p>
        <a href="dashboard.php" class="btn btn-custom">Back to Dashboard</a>
    </div>
</body>
</html>
