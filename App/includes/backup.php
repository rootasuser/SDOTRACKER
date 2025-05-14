<?php
require_once '../../Config/Config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

use App\Config\Config;

if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

$user = $_SESSION['user'];
$username = htmlspecialchars($user['username']); 
$backupFolder = "backups/";
$backupFilename = "database_backup_" . date("Y-m-d_H-i-s") . ".sql";
$backupFilePath = $backupFolder . $backupFilename;

if (!is_dir($backupFolder) && !mkdir($backupFolder, 0777, true) && !is_dir($backupFolder)) {
    die("Failed to create backup directory.");
}

function verifyPasscode($inputPasscode)
{
    try {
        $config = new Config();
        $pdo = new PDO("mysql:host={$config->DB_HOST};dbname={$config->DB_NAME}", $config->DB_USER, $config->DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->query("SELECT passcode_hash FROM backup_passcode ORDER BY id DESC LIMIT 1");
        $hash = $stmt->fetchColumn();

        if (!$hash) {
            return false; 
        }

        return password_verify($inputPasscode, $hash);
    } catch (PDOException $e) {
        return false;
    }
}

function createDatabaseBackup($username, $backupFilePath)
{
    try {
        $config = new Config();
        $host = $config->DB_HOST;
        $dbUser = $config->DB_USER;
        $dbPass = $config->DB_PASS;
        $database = $config->DB_NAME;

        $mysqldumpPath = "C:\\xampp\\mysql\\bin\\mysqldump.exe"; 

        $command = sprintf(
            "\"%s\" --user=%s --password=%s --host=%s %s > %s 2>&1",
            $mysqldumpPath,
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($backupFilePath)
        );
        
        shell_exec($command);
        


        if (!file_exists($backupFilePath)) {
            throw new Exception("Backup creation failed.");
        }

        logBackupAction($username);

        return "<a href='$backupFilePath' download>Download Backup database here.</a>";
    } catch (Exception $e) {
        return "Err: " . htmlspecialchars($e->getMessage());
    }
}

function logBackupAction($username)
{
    try {
        $config = new Config();
        $pdo = new PDO("mysql:host={$config->DB_HOST};dbname={$config->DB_NAME}", $config->DB_USER, $config->DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $logMessage = "$username successfully generated a database backup.";
        $stmt = $pdo->prepare("INSERT INTO logs (username, action, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$username, $logMessage]);
    } catch (PDOException $e) {
        die("Err logging action.");
    }
}

$notificationMessage = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['backup'])) 
{
    if (!empty($_POST['passcode']) && verifyPasscode($_POST['passcode'])) 
    {
        $notificationMessage = createDatabaseBackup($username, $backupFilePath);
    } else {
        $notificationMessage = "Incorrect passcode. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Backup</title>
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <h2 class="mb-4 text-dark">Database Backup Generator</h2>
        <form method="POST">
            <input type="password" name="passcode" placeholder="Enter Passcode" required class="form-control mb-3">
            <div class="mb-3">
            <span style="color: red;">For database backup requests, please contact the system administrator.</span>
            </div>
            <button type="submit" name="backup" class="btn btn-dark btn-xl" style="background-color: #000; color: #fff;">
                <i class="fas fa-download"></i> Generate
            </button>
        </form>
        <?php if (!empty($notificationMessage)): ?>
            <div class="mt-4 alert alert-info">
                <?= $notificationMessage ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
