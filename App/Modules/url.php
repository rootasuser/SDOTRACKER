<?php

require_once '../Config/Config.php';

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;  // PDO connection

// Get the parameters from the URL
$school_id = isset($_GET['school_id']) ? $_GET['school_id'] : null;
$position_id = isset($_GET['position_id']) ? $_GET['position_id'] : null;
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : null;

// Start building the SQL query based on the parameters
$query = "SELECT * FROM employees WHERE 1";

// Add condition for school_id if it exists
if ($school_id) {
    $query .= " AND empAssignSchool = :school_id";
}

// Add condition for position_id if it exists
if ($position_id) {
    $query .= " AND empPosition = :position_id";
}

// Add condition for subject_id if it exists
if ($subject_id) {
    $query .= " AND empTeachingSubject = :subject_id";
}

$stmt = $conn->prepare($query);

// Bind parameters if they exist
if ($school_id) {
    $stmt->bindParam(':school_id', $school_id);
}
if ($position_id) {
    $stmt->bindParam(':position_id', $position_id);
}
if ($subject_id) {
    $stmt->bindParam(':subject_id', $subject_id);
}

// Execute the query
$stmt->execute();

// Fetch all employees based on the filters
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-2">
    <h3>Employee List</h3>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>School</th>
                <th>Position</th>
                <th>Subject</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display the employee data
            foreach ($employees as $employee) {
                ?>
                <tr>
                    <td><?php echo $employee['id']; ?></td>
                    <td><?php echo $employee['name']; ?></td>
                    <td><?php echo $employee['empAssignSchool']; ?></td>
                    <td><?php echo $employee['empPosition']; ?></td>
                    <td><?php echo $employee['empTeachingSubject']; ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

