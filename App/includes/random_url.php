<?php
require_once '../../Config/Config.php';

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;  // PDO connection

// Get the random ID from the URL
$randomId = isset($_GET['id']) ? $_GET['id'] : null;

// Fetch data based on the random ID (you can customize how you process the random ID here)
$query = "SELECT * FROM employees WHERE randomId = :randomId"; // Assuming your table has a column 'randomId'
$stmt = $conn->prepare($query);
$stmt->bindParam(':randomId', $randomId, PDO::PARAM_STR);
$stmt->execute();

// Fetch the data
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="employee-details">
    <h3>Employee Details</h3>
    <p>Name: <?php echo $employee['empName']; ?></p>
    <p>Position: <?php echo $employee['empPosition']; ?></p>
    <p>School: <?php echo $employee['empAssignSchool']; ?></p>
    <p>Subject: <?php echo $employee['empTeachingSubject']; ?></p>
    <p>Created At: <?php echo $employee['created_at']; ?></p>
</div>
