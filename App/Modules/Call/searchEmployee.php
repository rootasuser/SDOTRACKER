<?php
require '../../Config/Config.php';

use App\Config\Config;
use PDO;
use PDOException;

header('Content-Type: application/json');

session_start();  

$response = [
    'status' => 'error',
    'data' => []
];

try {
    // Check if query parameter is set
    if (isset($_POST['query'])) {
        $query = $_POST['query'];

        // Prepare SQL query to search employee by name or number
        $stmt = $conn->prepare("
            SELECT e.id, e.empName, e.empNumber, e.empAddress, e.empSex, e.empPosition, 
                   e.empAssignSchool, e.empTeachingSubject, e.empHistory, e.created_at
            FROM employees e
            WHERE e.empName LIKE :query OR e.empNumber LIKE :query
        ");
        
        // Bind parameter to prevent SQL injection
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        
        // Execute the statement
        $stmt->execute();

        // Fetch the results
        if ($stmt->rowCount() > 0) {
            $employees = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $employees[] = $row; // Add each employee's data to the array
            }

            // Prepare the response data
            $response['status'] = 'success';
            $response['data'] = $employees;
        } else {
            $response['status'] = 'no_results';
        }
    }
} catch (PDOException $e) {
    // Handle errors
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

// Return the response as JSON
echo json_encode($response);

// Close the connection
$conn = null;
?>
