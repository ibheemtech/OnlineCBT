<?php
session_start();
require 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, is_active FROM users WHERE username = ?");
    if ($stmt === false) {
        die('SQL Error: ' . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $is_active);
        $stmt->fetch();

        if ($is_active) {
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;

                // Set a session variable to indicate successful login
                $_SESSION['login_success'] = "Welcome {$username} to this online CBT. Best of luck!";

                header("Location: login.php"); // Reload the page to trigger the alert
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Your account is inactive. Please contact the administrator.";
        }
    } else {
        $error = "Username not found. Please contact the administrator.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-body">
                        <h2 class="card-title text-center">Login</h2>
                        <div id="error" class="alert alert-danger <?php if(empty($error)) echo 'd-none'; ?>">
                            <?php echo $error; ?>
                        </div>
                        <form id="loginForm" action="login.php" method="post">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required disabled>
                            </div>
                            <button type="submit" id="loginButton" class="btn btn-primary btn-block" disabled>Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#username').on('input', function() {
                var username = $(this).val();
                
                if (username.length > 0) {
                    $.ajax({
                        url: 'user/check_username.php',
                        type: 'POST',
                        data: { username: username },
                        success: function(response) {
                            if (response == 'available') {
                                $('#error').addClass('d-none');
                                $('#password').prop('disabled', false);
                                $('#loginButton').prop('disabled', false);
                            } else {
                                $('#error').text('Username not found or inactive. Please contact the administrator.').removeClass('d-none');
                                $('#password').prop('disabled', true);
                                $('#loginButton').prop('disabled', true);
                            }
                        }
                    });
                } else {
                    $('#error').addClass('d-none');
                    $('#password').prop('disabled', true);
                    $('#loginButton').prop('disabled', true);
                }
            });

            <?php if(isset($_SESSION['login_success'])): ?>
            Swal.fire({
                title: 'Login successful!',
                text: "<?php echo $_SESSION['login_success']; ?>",
                icon: 'success',
                showConfirmButton: false,
                timer: 3000,
                didClose: () => {
                    window.location.href = 'user/dashboard.php';
                }
            });
            <?php unset($_SESSION['login_success']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
