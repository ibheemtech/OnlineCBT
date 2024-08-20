<?php
include 'header.php';

$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entries_per_page;
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$sql_total = "SELECT COUNT(*) AS total FROM courses WHERE course_name LIKE '%$search_query%'";
$result_total = $conn->query($sql_total);
$total_courses = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $entries_per_page);

$sql = "SELECT course_id, course_name, timer, total_questions, 
        CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END AS status 
        FROM courses 
        WHERE course_name LIKE '%$search_query%' 
        LIMIT $entries_per_page OFFSET $offset";
$result = $conn->query($sql);
?>

<div class="container mt-4">
    <h1>Manage Courses</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <label for="entries" class="form-label">Show</label>
            <select id="entries" class="form-select d-inline-block w-auto" onchange="changeEntries(this.value);">
                <option value="10" <?= $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?= $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?= $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?= $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
            </select>
            <span>entries</span>
        </div>
        <div class="d-flex align-items-center">

            <input type="text" id="search" class="form-control w-auto" placeholder="Search..." value="<?= htmlspecialchars($search_query); ?>">
            <button id="searchButton" class="btn btn-primary ms-2" onclick="searchCourses()">Search</button>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Timer (mins)</th>
                    <th>Total Questions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="courseTable">
                <?php while ($course = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $course['course_id']; ?></td>
                        <td><?= $course['course_name']; ?></td>
                        <td><?= $course['timer']; ?></td>
                        <td><?= $course['total_questions']; ?></td>
                        <td>
    <span class="<?= $course['status'] == 'Active' ? 'btn-success' : 'btn-danger'; ?>">
        <?= $course['status'] == 'Active' ? 'Active' : 'Inactive'; ?>
    </span>
</td>

                        <td>
    <a href="edit_course.php?id=<?= $course['course_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
    <a href="course/toggle_course.php?id=<?= $course['course_id']; ?>&status=<?= $course['status'] == 'Active' ? 0 : 1; ?>" 
       class="btn btn-sm <?= $course['status'] == 'Active' ? 'btn-success' : 'btn-danger'; ?>">
        <?= $course['status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
    </a>
    <a href="course/delete_course.php?id=<?= $course['course_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
</td>


                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span>Showing <?= ($offset + 1); ?> to <?= min($offset + $entries_per_page, $total_courses); ?> of <?= $total_courses; ?> entries</span>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page - 1; ?>&entries=<?= $entries_per_page; ?>&search=<?= $search_query; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>&entries=<?= $entries_per_page; ?>&search=<?= $search_query; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page + 1; ?>&entries=<?= $entries_per_page; ?>&search=<?= $search_query; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>

    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            No courses found for your search query.
        </div>
    <?php endif; ?>

    <br>
    <a href="add_course.php" class="btn btn-primary">Add New Course</a>
</div>

<?php
include 'footer.php';
$conn->close();
?>
