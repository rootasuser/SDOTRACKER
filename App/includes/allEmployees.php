<?php
require_once '../../Config/Config.php';

class AllEmployees {
    private $conn;

    public function __construct() {
        $config = new \App\Config\Config();
        $this->conn = $config->DB_CONNECTION;
    }

    public function getAllEmployees() {
        $query = "
            SELECT 
                e.*,
                s.empAssignSchool,
                p.empPosition,
                GROUP_CONCAT(sub.empTeachingSubject SEPARATOR ', ') AS empTeachingSubject,
                GROUP_CONCAT(DISTINCT t.title SEPARATOR ', ') AS trainingAttended,
                GROUP_CONCAT(DISTINCT t.date_conducted SEPARATOR ', ') AS trainingConducted,
                GROUP_CONCAT(DISTINCT t.venue SEPARATOR ', ') AS trainingVenue
            FROM employees e
            LEFT JOIN schools s ON e.empAssignSchool_id = s.id
            LEFT JOIN positions p ON e.empPosition_id = p.id
            LEFT JOIN employee_trainings et ON e.id = et.employee_id
            LEFT JOIN trainings t ON et.training_id = t.id
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
<style>
   /**
        Swipe table
    */
    .table-responsive {
    overflow-x: auto;
    white-space: nowrap;
    cursor: grab; 
    }

    .table-responsive:active {
        cursor: grabbing;
    }
   
</style>



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
<div class="d-flex align-items-center mb-3">
    <button class="btn btn-light mr-2" style="color: #fff; background-color: #000;" onclick="printTable()">
        <i class="fas fa-print"></i> Print
    </button>
    <button class="btn btn-light mr-2" style="color: #fff; background-color: #000;" onclick="exportTableToCSV('employees.csv')">
        <i class="fas fa-file-export"></i> Export CSV
    </button>
    <span class="ml-auto" style="font-size: 0.875rem; color: #000;">
    Please be advised that this section is restricted to viewing, searching, and deleting records only.
    </span>
</div>
    <!-- Employee Table -->
    <?php if (empty($employees)): ?>
        <div class="alert alert-info">No employees found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="dataTable">
                <thead class="table" style="background-color: #000; color: #fff;">
                    <tr>
                        <th style="font-size: 12px; white-space: nowrap;">#</th>
                        <th style="font-size: 12px; white-space: nowrap;">Full Name</th>
                        <th style="font-size: 12px; white-space: nowrap;">Employee Number</th>
                        <th style="font-size: 12px; white-space: nowrap;">Employee Status</th>
                        <th style="font-size: 12px; white-space: nowrap;">Date of Birth</th>
                        <th style="font-size: 12px; white-space: nowrap;">Civil Status</th>
                        <th style="font-size: 12px; white-space: nowrap;">Tin #</th>
                        <th style="font-size: 12px; white-space: nowrap;">Plantilla #</th>
                        <th style="font-size: 12px; white-space: nowrap;">District</th>
                        <th style="font-size: 12px; white-space: nowrap;">Gender</th>
                        <th style="font-size: 12px; white-space: nowrap;">Address</th>
                        <th style="font-size: 12px; white-space: nowrap;">Assigned School</th>
                        <th style="font-size: 12px; white-space: nowrap;">Position</th>
                        <th style="font-size: 12px; white-space: nowrap;">Teaching Subject/s</th>
                        <th style="font-size: 12px; white-space: nowrap;">School History</th>
                        <th style="font-size: 12px; white-space: nowrap;">Training Attended</th>
                        <th style="font-size: 12px; white-space: nowrap;">Training Conducted</th>
                        <th style="font-size: 12px; white-space: nowrap;">Training Venue</th>
                        <th style="font-size: 12px; white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['id']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
                                <a href="#" 
                                class="employee-name-link"
                                data-employee-name="<?= urlencode(htmlspecialchars($employee['empName'])) ?>">
                                    <?= htmlspecialchars($employee['empName']) ?>
                                </a>
                            </td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empNumber']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; text-align: center; color: #000;">
                            <?php
                            $status = htmlspecialchars($employee['empStatus']);
                            $badgeClass = '';
                            switch ($status) {
                                case 'Active':
                                    $badgeClass = 'badge bg-success text-white';
                                    break;
                                case 'Inactive':
                                    $badgeClass = 'badge bg-secondary text-white';
                                    break;
                                case 'On Leave':
                                    $badgeClass = 'badge bg-warning text-white';
                                    break;
                                case 'Retired':
                                    $badgeClass = 'badge bg-info text-white';
                                    break;
                                case 'Terminated':
                                    $badgeClass = 'badge bg-danger text-white';
                                    break;
                                default:
                                    $badgeClass = 'badge bg-light text-white';
                            }
                            ?>
                            <span class="<?= $badgeClass ?>"><?= $status ?></span>
                        </td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empDob']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empCS']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empTinNum']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empPlantilla']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empDistrict']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empSex']) ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empAddress']) ?></td>
                    
                            <!-- Assigned School -->
                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
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
                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
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
                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
                                <a href="#" 
                                   class="text-dark show-modal" 
                                   data-bs-toggle="modal"
                                   data-bs-target="#employeeModal"
                                   data-teaching-subject="<?= htmlspecialchars($employee['empTeachingSubject'] ?? 'N/A') ?>"
                                   data-emp-id="<?= htmlspecialchars($employee['id']) ?>">
                                    <?= htmlspecialchars($employee['empTeachingSubject'] ?? 'N/A') ?>
                                </a>
                            </td>

                            <td style="font-size: 12px; white-space: nowrap; color: #000;"><?= htmlspecialchars($employee['empHistory']) ?></td>

                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
                                <a href="#" 
                                class="text-dark show-modal" 
                                data-bs-toggle="modal"
                                data-bs-target="#employeeModal"
                                data-training-attended="<?= htmlspecialchars($employee['trainingAttended'] ?? 'N/A') ?>"
                                data-training-conducted="<?= htmlspecialchars($employee['trainingConducted'] ?? 'N/A') ?>"
                                data-training-venue="<?= htmlspecialchars($employee['trainingVenue'] ?? 'N/A') ?>"
                                data-emp-id="<?= htmlspecialchars($employee['id']) ?>">
                                    <?= htmlspecialchars($employee['trainingAttended'] ?? 'N/A') ?>
                                </a>
                            </td>

                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
                                <?= htmlspecialchars($employee['trainingConducted'] ?? 'N/A') ?>
                            </td>

                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
                                <?= htmlspecialchars($employee['trainingVenue'] ?? 'N/A') ?>
                            </td>

                            <!-- Actions -->
                            <td style="font-size: 12px; white-space: nowrap; color: #000;">
                                <form action="" method="POST">
                                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($employee['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
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
        <p id="modal-training-attended"></p>
        <p id="modal-training-conducted"></p>
        <p id="modal-training-venue"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!--  SortableJS -->
<script>
   // ====> Swipe Tbl
document.addEventListener("DOMContentLoaded", function () {
    const tableContainer = document.querySelector(".table-responsive");
    let isDown = false;
    let startX;
    let scrollLeft;

    tableContainer.addEventListener("mousedown", (e) => {
        isDown = true;
        tableContainer.classList.add("active");
        startX = e.pageX - tableContainer.offsetLeft;
        scrollLeft = tableContainer.scrollLeft;
    });

    tableContainer.addEventListener("mouseleave", () => {
        isDown = false;
        tableContainer.classList.remove("active");
    });

    tableContainer.addEventListener("mouseup", () => {
        isDown = false;
        tableContainer.classList.remove("active");
    });

    tableContainer.addEventListener("mousemove", (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - tableContainer.offsetLeft;
        const walk = (x - startX) * 2; 
        tableContainer.scrollLeft = scrollLeft - walk;
    });
});


// ==> D.Table
$(document).ready(function () {

    if ($('#dataTable').length) {
    $('#dataTable').DataTable({
        paging: true,
        lengthChange: true, 
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        pageLength: 50,
        lengthMenu: [ [50, 100, 500, 1000, 5000], [50, 100, 500, 1000, 5000] ]  // show entries
    });
}

    

    $('.show-modal').on('click', function () {
        const assignSchool = $(this).data('assign-school') || 'N/A';
        const position = $(this).data('position') || 'N/A';
        const teachingSubject = $(this).data('teaching-subject') || 'N/A';
        const trainingAttended = $(this).data('training-attended') || 'N/A';
        const trainingConducted = $(this).data('training-conducted') || 'N/A';
        const trainingVenue = $(this).data('training-venue') || 'N/A';
        
        $('#modal-assign-school').text('Assigned School: ' + assignSchool);
        $('#modal-position').text('Position: ' + position);
        $('#modal-teaching-subject').text('Teaching Subject: ' + teachingSubject);
        $('#modal-training-attended').text('Training Attended: ' + trainingAttended);
        $('#modal-training-conducted').text('Training Conducted: ' + trainingConducted);
        $('#modal-training-venue').text('Training Venue: ' + trainingVenue);
    });
});

function printTable() {
    var table = document.getElementById('dataTable');
    var printWindow = window.open('', '', 'width=600,height=400');

    var rows = table.rows;
    for (var i = 0; i < rows.length; i++) {
        rows[i].deleteCell(rows[i].cells.length - 1);
    }

    printWindow.document.write('<html><head><title>Employees List</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('th, td { padding: 8px 12px; text-align: justify; border: 1px solid black; color: #000; }');
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
        var row = [];
        var cols = rows[i].querySelectorAll("td, th");

        // Include all columns except the last one (actions)
        for (var j = 0; j < cols.length - 1; j++) { 
            // Check if the cell has any content
            var cellText = cols[j].textContent || cols[j].innerText || '';
            row.push('"' + cellText.replace(/"/g, '""') + '"');
        }

        csv.push(row.join(","));
    }

    var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
    var downloadLink = document.createElement("a");
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = filename;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>