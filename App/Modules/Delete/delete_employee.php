<?php
require_once '../../Config/Config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    $employeeId = intval($_POST["delete_id"]);
    $username = htmlspecialchars(trim($_POST['username'] ?? 'Unknown User'));

    // Fetch employee name before deletion for logging
    $fetchQuery = "SELECT empName FROM employees WHERE id = :id";
    $fetchStmt = $conn->prepare($fetchQuery);
    $fetchStmt->bindParam(':id', $employeeId, PDO::PARAM_INT);
    $fetchStmt->execute();
    $employee = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo json_encode(["status" => "error", "message" => "Employee not found."]);
        exit;
    }

    $empName = htmlspecialchars($employee['empName']);

    // Delete employee
    $deleteQuery = "DELETE FROM employees WHERE id = :id";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $employeeId, PDO::PARAM_INT);

    if ($deleteStmt->execute()) {
        // Insert log into the logs table
        $logQuery = "INSERT INTO logs (username, action, created_at) VALUES (:username, :action, NOW())";
        $logStmt = $conn->prepare($logQuery);
        $logAction = "$username successfully deleted $empName's information.";

        $logStmt->bindParam(':username', $username);
        $logStmt->bindParam(':action', $logAction);
        $logStmt->execute();

        echo json_encode(["status" => "success", "message" => "Employee deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete employee."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
