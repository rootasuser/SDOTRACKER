<?php
session_start();

require_once __DIR__ . '/../../Config/Config.php';

use App\Config\Config;

$config = new Config();
$conn   = $config->DB_CONNECTION;

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bindParam(1, $delete_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Subject deleted successfully';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to delete subject';
        $_SESSION['message_type'] = 'danger';
    }

    $redirectUrl = htmlspecialchars($_SERVER['PHP_SELF']);
    exit();
}
