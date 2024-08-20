

<?php include '../includes/header.php'; ?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="#availableCourses" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-book"></i> Available Courses</a>
    <div id="availableCourses" class="collapse">
        <?php while($course = $courses_result->fetch_assoc()): ?>
            <?php if ($course['is_active'] == 1): ?>
                <a href="course_details.php?course_id=<?php echo $course['course_id']; ?>">
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </a>
            <?php else: ?>
                <a href="#" class="pl-4 text-muted" title="Course is not active">
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </a>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
    <a href="#manageResults" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-chart-bar"></i> Manage Results</a>
    <div id="manageResults" class="collapse">
        <a href="courses.php" class="pl-4"><i class="fas fa-eye"></i> View Results</a>
     
    </div>
</div>

<div class="content" id="content">

<?php include '../includes/footer.php'; ?>
