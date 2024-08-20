<?php
// Include necessary files and start session
include 'db.php';
include('header.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Admin not logged in.");
}

// Fetching statistics
$queries = [
    'total_courses' => "SELECT COUNT(*) as total FROM courses",
    'total_questions' => "SELECT COUNT(*) as total FROM questions",
    'total_results' => "SELECT COUNT(*) as total FROM course_results",
    'total_submissions' => "SELECT COUNT(*) as total FROM exam_results",
    'total_users' => "SELECT COUNT(*) as total FROM users"
];

$results = [];
foreach ($queries as $key => $query) {
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $results[$key] = $row['total'];
    } else {
        $results[$key] = 0;
    }
}

$admin_name = isset($admin['name']) ? $admin['name'] : 'Admin';
?>

<!-- Optional: Custom CSS -->
<style>
    .card-custom {
        border-radius: 10px;
        color: #fff;
        margin-bottom: 20px;
    }
    .card-custom .card-body {
        text-align: center;
    }
    .card-custom h5 {
        font-size: 1.25rem;
        margin-bottom: 10px;
    }
    .card-custom p {
        font-size: 2rem;
        font-weight: bold;
    }
</style>

<div class="container mt-5">
    <h1>Welcome to the Admin Dashboard, <?php echo htmlspecialchars($admin_name); ?>!</h1>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card card-custom bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Courses</h5>
                    <p class="card-text"><?php echo number_format($results['total_courses']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Questions</h5>
                    <p class="card-text"><?php echo number_format($results['total_questions']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Results</h5>
                    <p class="card-text"><?php echo number_format($results['total_results']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mt-4">
            <div class="card card-custom bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Total Submissions</h5>
                    <p class="card-text"><?php echo number_format($results['total_submissions']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mt-4">
            <div class="card card-custom bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text"><?php echo number_format($results['total_users']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
