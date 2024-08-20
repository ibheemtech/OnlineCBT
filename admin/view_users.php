<?php
include 'db.php';
include 'header.php';

// Handle entries per page and search query
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Set the current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entries_per_page;

// Search query filter
$search_sql = $search_query ? "WHERE username LIKE '%$search_query%' OR email LIKE '%$search_query%' OR name LIKE '%$search_query%'" : "";

// Count total records
$total_records_query = "SELECT COUNT(*) as total FROM users $search_sql";
$total_records_result = $conn->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['total'];

// Fetch users with limit for pagination
$users_query = "SELECT id, username, email, name, profile_picture, is_active FROM users $search_sql LIMIT $offset, $entries_per_page";
$users_result = $conn->query($users_query);
if (!$users_result) {
    die("Error fetching users: " . $conn->error);
}

// Calculate total pages
$total_pages = ceil($total_records / $entries_per_page);
?>

<div class="container mt-4">
    <h1>View Users</h1>
    <br>
    <a href="add_users.php" class="btn btn-primary">Add New User</a>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <label for="entries" class="form-label">Show</label>
            <select id="entries" class="form-select d-inline-block w-auto" onchange="location = this.value;">
                <option value="?course_id=<?= $course_id ?>&entries=10" <?= $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                <option value="?course_id=<?= $course_id ?>&entries=25" <?= $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                <option value="?course_id=<?= $course_id ?>&entries=50" <?= $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                <option value="?course_id=<?= $course_id ?>&entries=100" <?= $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
            </select>
            <span>entries</span>
        </div>
        <form method="get" class="d-flex align-items-center">
            <input type="hidden" name="course_id" value="<?= $course_id; ?>">
            <input type="hidden" name="entries" value="<?= $entries_per_page; ?>">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?= htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Display Entries Range -->
    <div>
        <span>Showing <?= ($offset + 1); ?> to <?= min($offset + $entries_per_page, $total_records); ?> of <?= $total_records; ?> entries</span>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Profile Picture</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']); ?></td>
                    <td><?= htmlspecialchars($user['name']); ?></td>
                    <td><?= htmlspecialchars($user['username']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php if ($user['profile_picture']): ?>
                            <img src="uploads/<?= htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" width="50">
                        <?php else: ?>
                            No picture
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="color: <?= $user['is_active'] ? 'blue' : 'red'; ?>;">
                            <?= $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_users.php?id=<?= $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_users.php?id=<?= $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?course_id=<?= $course_id; ?>&search=<?= urlencode($search_query); ?>&entries=<?= $entries_per_page; ?>&page=<?= ($page - 1); ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?course_id=<?= $course_id; ?>&search=<?= urlencode($search_query); ?>&entries=<?= $entries_per_page; ?>&page=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?course_id=<?= $course_id; ?>&search=<?= urlencode($search_query); ?>&entries=<?= $entries_per_page; ?>&page=<?= ($page + 1); ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<?php
include 'footer.php';
$conn->close();
?>
