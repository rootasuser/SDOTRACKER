<?php
require_once '../../Config/Config.php';

class AllEmployees {
    private $conn;

    public function __construct() {
        $config = new \App\Config\Config();
        $this->conn = $config->DB_CONNECTION;
    }

    // Updated method: join related tables so that the human‑readable names are returned
    public function getAllEmployees() {
        $query = "
            SELECT 
                e.*,
                s.empAssignSchool,
                p.empPosition,
                GROUP_CONCAT(sub.empTeachingSubject SEPARATOR ', ') AS empTeachingSubject
            FROM employees e
            LEFT JOIN schools s ON e.empAssignSchool_id = s.id
            LEFT JOIN positions p ON e.empPosition_id = p.id
            LEFT JOIN employee_subjects es ON e.id = es.employee_id
            LEFT JOIN subjects sub ON es.subject_id = sub.id
            GROUP BY e.id
            ORDER BY e.empName ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteEmployee($id) {
        $query = "SELECT empName, empNumber FROM employees WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            $deleteQuery = "DELETE FROM employees WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteResult = $deleteStmt->execute();

            if ($deleteResult) {
                $this->logAction($employee['empName'], $employee['empNumber']);
            }

            return $deleteResult;
        }

        return false;
    }

    private function logAction($empName, $empNumber) {
        if (isset($_SESSION['user']['username'])) {
            $username = $_SESSION['user']['username'];
        } else {
            $username = 'Unknown User'; 
        }

        $logQuery = "INSERT INTO logs (username, action) VALUES (:username, :action)";
        $logStmt = $this->conn->prepare($logQuery);
        $action = "$username successfully deleted $empName, $empNumber";
        $logStmt->bindParam(':username', $username);
        $logStmt->bindParam(':action', $action);
        $logStmt->execute();
    }
}

$employeeManager = new AllEmployees();
$employees = $employeeManager->getAllEmployees();

$successMessage = '';
$errorMessage = '';

if (isset($_POST['delete_id'])) {
    $employeeId = $_POST['delete_id'];
    if ($employeeManager->deleteEmployee($employeeId)) {
        $successMessage = "Employee deleted successfully!";
    } else {
        $errorMessage = "Failed to delete employee. Please try again.";
    }
}
?>

<div class="container mt-4">
    <!-- Success/Error Notifications -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $successMessage; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $errorMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Print & Export Buttons -->
    <div class="mb-3">
        <button class="btn btn-dark" onclick="printTable()"><i class="fas fa-print"></i> Print</button>
        <button class="btn btn-dark" onclick="exportTableToCSV('employees.csv')"><i class="fas fa-file-export"></i> Export CSV</button>
    </div>

    <!-- Employee Table -->
    <?php if (empty($employees)): ?>
        <div class="alert alert-info">No employees found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="dataTable">
                <thead class="table-dark">
                    <tr>
                        <th style="font-size: 12px; white-space: nowrap;">#</th>
                        <th style="font-size: 12px; white-space: nowrap;">Name</th>
                        <th style="font-size: 12px; white-space: nowrap;">Number</th>
                        <th style="font-size: 12px; white-space: nowrap;">Gender</th>
                        <th style="font-size: 12px; white-space: nowrap;">Address</th>
                        <th style="font-size: 12px; white-space: nowrap;">Assigned School</th>
                        <th style="font-size: 12px; white-space: nowrap;">Position</th>
                        <th style="font-size: 12px; white-space: nowrap;">Teaching Subject/s</th>
                        <th style="font-size: 12px; white-space: nowrap;">Schoo History</th>
                        <th style="font-size: 12px; white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td style="font-size: 12px; white-space: nowrap;"><?= htmlspecialchars($employee['id']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap;"><?= htmlspecialchars($employee['empName']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap;"><?= htmlspecialchars($employee['empNumber']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap;"><?= htmlspecialchars($employee['empSex']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap;"><?= htmlspecialchars($employee['empAddress']) ?></td>
                    
                            <!-- Assigned School -->
                            <td style="font-size: 12px; white-space: nowrap;">
                                <a href="#" 
                                   class="text-dark show-modal" 
                                   data-bs-toggle="modal"
                                   data-bs-target="#employeeModal"
                                   data-assign-school="<?= htmlspecialchars($employee['empAssignSchool'] ?? 'N/A') ?>"
                                   data-emp-id="<?= htmlspecialchars($employee['id']) ?>">
                                    <?= htmlspecialchars($employee['empAssignSchool'] ?? 'N/A') ?>
                                </a>
                            </td>

                            <!-- Position -->
                            <td style="font-size: 12px; white-space: nowrap;">
                                <a href="#" 
                                   class="text-dark show-modal" 
                                   data-bs-toggle="modal"
                                   data-bs-target="#employeeModal"
                                   data-position="<?= htmlspecialchars($employee['empPosition'] ?? 'N/A') ?>"
                                   data-emp-id="<?= htmlspecialchars($employee['id']) ?>">
                                    <?= htmlspecialchars($employee['empPosition'] ?? 'N/A') ?>
                                </a>
                            </td>

                            <!-- Teaching Subjects -->
                            <td style="font-size: 12px; white-space: nowrap;">
                                <a href="#" 
                                   class="text-dark show-modal" 
                                   data-bs-toggle="modal"
                                   data-bs-target="#employeeModal"
                                   data-teaching-subject="<?= htmlspecialchars($employee['empTeachingSubject'] ?? 'N/A') ?>"
                                   data-emp-id="<?= htmlspecialchars($employee['id']) ?>">
                                    <?= htmlspecialchars($employee['empTeachingSubject'] ?? 'N/A') ?>
                                </a>
                            </td>

                            <td style="font-size: 12px; white-space: nowrap;"><?= htmlspecialchars($employee['empHistory']) ?></td>
                            <!-- Actions -->
                            <td style="font-size: 12px; white-space: nowrap;">
                                <form action="" method="POST">
                                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($employee['id']) ?>">
                                    <button type="submit" class="btn btn-dark btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="employeeModalLabel">Employee Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="modal-assign-school"></p>
        <p id="modal-position"></p>
        <p id="modal-teaching-subject"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {

    if ($('#dataTable').length) {
        $('#dataTable').DataTable({
            paging: true,
            lengthChange: false,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            pageLength: 5
        });
    }
    

    $('.show-modal').on('click', function () {
        const assignSchool = $(this).data('assign-school') || 'N/A';
        const position = $(this).data('position') || 'N/A';
        const teachingSubject = $(this).data('teaching-subject') || 'N/A';
        
        $('#modal-assign-school').text('Assigned School: ' + assignSchool);
        $('#modal-position').text('Position: ' + position);
        $('#modal-teaching-subject').text('Teaching Subject: ' + teachingSubject);
    });
});

function printTable() {
    var table = document.getElementById('dataTable');
    var printWindow = window.open('', '', 'width=600,height=400');

    var rows = table.rows;
    for (var i = 0; i < rows.length; i++) {
        rows[i].deleteCell(rows[i].cells.length - 1);
    }

    printWindow.document.write('<html><head><title>Print Table</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('th, td { padding: 8px 12px; text-align: justify; border: 1px solid black; }');
    printWindow.document.write('th { background-color: #f2f2f2; }');
    printWindow.document.write('</style></head><body>');
    
    printWindow.document.write('<h1>Employees List</h1>');
    printWindow.document.write(table.outerHTML);
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    
    printWindow.onload = function () {
        printWindow.print();
        printWindow.close();
    };
}

function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("#dataTable tr");
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(row.join(","));
    }
    
    var csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    var downloadLink = document.createElement("a");
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = filename;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
