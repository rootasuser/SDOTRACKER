<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/Config.php';

if (isset($_SESSION['user'])) {
 
}

function logAction($username, $action) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO logs (username, action) VALUES (?, ?)");
        $stmt->execute([$username, $action]);
    } catch (PDOException $e) {
        error_log("Log action failed: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $_SESSION['message'] = 'All fields are required.';
        $_SESSION['message_type'] = 'error';
    } elseif ($password !== $confirm_password) {
        $_SESSION['message'] = 'Passwords do not match.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $config = new \App\Config\Config();
            $conn = $config->DB_CONNECTION;
            $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = 'Username already exists. Please choose another one.';
                $_SESSION['message_type'] = 'error';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $conn->beginTransaction();
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashed_password]);

                logAction($username, "successfully created.");

                $conn->commit();

                $_SESSION['message'] = 'Account created successfully!';
                $_SESSION['message_type'] = 'success';
            }
        } catch (PDOException $e) {
            if ($conn) {
                $conn->rollBack();
            }
            logAction($username, "failed to create account: " . $e->getMessage());
            $_SESSION['message'] = 'Failed to create account. Please try again.';
            $_SESSION['message_type'] = 'error';
        }
    }

}
?>


    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center" style="color: #000;">Create Account</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($_SESSION['message'])) {
                            echo '<div class="alert alert-' . ($_SESSION['message_type'] === 'success' ? 'success' : 'danger') . '">' . $_SESSION['message'] . '</div>';
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                        }
                        ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label" style="color: #000;">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label" style="color: #000;">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label" style="color: #000;">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
