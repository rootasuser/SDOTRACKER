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
   
    if (isset($_POST['query'])) {
        $query = $_POST['query'];

        $stmt = $conn->prepare("
            SELECT e.id, e.empName, e.empNumber, e.empAddress, e.empSex, e.empPosition, 
                   e.empAssignSchool, e.empTeachingSubject, e.empHistory, e.created_at
            FROM employees e
            WHERE e.empName LIKE :query OR e.empNumber LIKE :query
        ");
     
        $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
        
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $employees = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $employees[] = $row; 
            }


            $response['status'] = 'success';
            $response['data'] = $employees;
        } else {
            $response['status'] = 'no_results';
        }
    }
} catch (PDOException $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

$conn = null;
?>
