<?php
require 'db_connection.php';

use App\Config\Config;

$db = new Config();
$conn = $db->DB_CONNECTION;

$school = $_GET['school'] ?? '';

if ($school) {
    $stmt = $conn->prepare("SELECT empName, empPosition, empNumber FROM employees WHERE empAssignSchool = :school");
    $stmt->bindParam(":school", $school, PDO::PARAM_STR);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($employees) {
        foreach ($employees as $row) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['empName']) . "</td>
                    <td>" . htmlspecialchars($row['empPosition']) . "</td>
                    <td>" . htmlspecialchars($row['empNumber']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3' class='text-center'>No employees found for this school.</td></tr>";
    }
}
?>
