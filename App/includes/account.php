<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$configFilePath = __DIR__ . '/../Config/Config.php';
if (!file_exists($configFilePath)) {
    die('Error: Config file missing at ' . $configFilePath);
}
require_once($configFilePath);

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;


if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

$user = $_SESSION['user'];
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    
        $id = $_POST['id'];
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm_password']);

        if (empty($username)) {
            throw new Exception('Username is required');
        }

        if (!empty($password)) {
            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match');
            }
            if (strlen($password) < 4) {
                throw new Exception('Password must be at least 4 characters');
            }
        }

        $updateFields = ['username = :username'];
        $params = [':id' => $id, ':username' => $username];

        if (!empty($password)) {
            $updateFields[] = 'password = :password';
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $logAction = "$username successfully updated information.";
        $logQuery = "INSERT INTO logs (username, action, created_at) VALUES (:username, :action, NOW())";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->execute([':username' => $username, ':action' => $logAction]);

        $message = 'Account updated successfully!';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<div class="container">
    <div class="card">
        <div class="card-header text-center" style="background-color: #20263e; color: #ffffff; font-size: 25px; font-weight: bolder;">
            Account Information
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div id="message-alert"></div>

            <form method="POST" onsubmit="return validatePassword()">
                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" 
                                   class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" name="password" id="password" 
                                   class="form-control" 
                                   placeholder="Leave blank to keep current password"
                                   minlength="4">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" 
                                   class="form-control" 
                                   placeholder="Confirm new password"
                                   minlength="4">
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-light" style="color: #fff; background-color: #000;">Update Account</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validatePassword() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const messageAlert = document.getElementById('message-alert');

    if (password !== confirmPassword) {
        messageAlert.innerHTML = '<div class="alert alert-danger">Passwords do not match.</div>';
        return false;
    }
    
    if (password.length > 0 && password.length < 4) {
        messageAlert.innerHTML = '<div class="alert alert-danger">Password must be at least 4 characters.</div>';
        return false;
    }
    
    return true;
}
</script>
