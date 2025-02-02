<?php
require_once __DIR__ . '/../Config/Config.php';

use App\Config\Config;

$config = new Config();
$conn   = $config->DB_CONNECTION;

// Fetch Positions, Schools, and Teaching Subjects for Select Options
$positions_sql = "SELECT DISTINCT empPosition FROM positions";
$schools_sql = "SELECT DISTINCT empAssignSchool FROM schools";
$subjects_sql = "SELECT DISTINCT empTeachingSubject FROM subjects";

$positions_stmt = $conn->prepare($positions_sql);
$positions_stmt->execute();
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

$schools_stmt = $conn->prepare($schools_sql);
$schools_stmt->execute();
$schools = $schools_stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects_stmt = $conn->prepare($subjects_sql);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

$employees = []; // Initialize empty employees array

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $positionFilter = $_POST['position'] ?? '';
    $schoolFilter = $_POST['school'] ?? '';
    $subjectFilter = $_POST['subject'] ?? '';

    // Fetch Employees based on selected filters
    $filter_sql = "
        SELECT e.id, e.empName, e.empNumber, e.empSex, e.empAddress, e.empHistory, 
               e.empPosition_id, e.empAssignSchool_id, es.subject_id, s.empTeachingSubject, 
               p.empPosition, sc.empAssignSchool 
        FROM employees e
        LEFT JOIN employee_subjects es ON e.id = es.employee_id
        LEFT JOIN subjects s ON es.subject_id = s.id
        LEFT JOIN positions p ON e.empPosition_id = p.id
        LEFT JOIN schools sc ON e.empAssignSchool_id = sc.id
        WHERE 1=1";

    // Apply filters if selected
    if ($positionFilter) {
        $filter_sql .= " AND p.empPosition = :position";
    }
    if ($schoolFilter) {
        $filter_sql .= " AND sc.empAssignSchool = :school";
    }
    if ($subjectFilter) {
        $filter_sql .= " AND s.empTeachingSubject = :subject";
    }

    $filter_stmt = $conn->prepare($filter_sql);

    // Bind values for filters
    if ($positionFilter) {
        $filter_stmt->bindValue(':position', $positionFilter);
    }
    if ($schoolFilter) {
        $filter_stmt->bindValue(':school', $schoolFilter);
    }
    if ($subjectFilter) {
        $filter_stmt->bindValue(':subject', $subjectFilter);
    }

    $filter_stmt->execute();
    $employees = $filter_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-2">
    <div class="card">
        <div class="card-body">
           <!-- Search Form -->
<form method="POST" class="form mb-3">
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="position">Position</label>
            <select name="position" class="form-control" id="position">
                <option value="">Select Position</option>
                <?php foreach ($positions as $position): ?>
                    <option value="<?php echo htmlspecialchars($position['empPosition']); ?>"><?php echo htmlspecialchars($position['empPosition']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="school">School</label>
            <select name="school" class="form-control" id="school">
                <option value="">Select School</option>
                <?php foreach ($schools as $school): ?>
                    <option value="<?php echo htmlspecialchars($school['empAssignSchool']); ?>"><?php echo htmlspecialchars($school['empAssignSchool']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="subject">Teaching Subject</label>
            <select name="subject" class="form-control" id="subject">
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo htmlspecialchars($subject['empTeachingSubject']); ?>"><?php echo htmlspecialchars($subject['empTeachingSubject']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search</button>
        </div>
    </div>
</form>


            <!-- Print and Export Buttons -->
            <div class="mb-3">
                <button onclick="printTable('employeeTable')" class="btn btn-dark"><i class="fas fa-print"></i> Print</button>
                <button onclick="exportToCSV('employeeTable')" class="btn btn-dark"><i class="fas fa-file-export"></i> Export to CSV</button>
            </div>

            <!-- Display Employees -->
            <?php if ($employees): ?>
                <h3>Employee/s</h3>
                <table class="table" id="employeeTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>School</th>
                            <th>Subject</th>
                            <th>Sex</th>
                            <th>Address</th>
                            <th>History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['id']); ?></td>
                                <td><?php echo htmlspecialchars($employee['empName']); ?></td>
                                <td><?php echo htmlspecialchars($employee['empPosition']); ?></td>
                                <td><?php echo htmlspecialchars($employee['empAssignSchool']); ?></td>
                                <td><?php echo htmlspecialchars($employee['empTeachingSubject']); ?></td>
                                <td><?php echo htmlspecialchars($employee['empSex']); ?></td>
                                <td><?php echo htmlspecialchars($employee['empAddress']); ?></td>
                                <td><?php echo htmlspecialchars($employee['empHistory']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No employees found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Function to print a specific table
function printTable(tableId) {
    var tableContent = document.getElementById(tableId).outerHTML;

    // Add CSS for vertical lines between table columns
    var styles = `
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th, td {
                border: 1px solid black; /* Add vertical lines */
                padding: 8px;
                text-align: left;
            }
        </style>
    `;
    
    var printWindow = window.open('', '', 'height=400,width=800');
    printWindow.document.write('<html><head><title>Print</title>' + styles + '</head><body>');
    printWindow.document.write(tableContent);
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.print();
}

// Function to export a table to CSV with table head
function exportToCSV(tableId) {
    var table = document.getElementById(tableId);
    var rows = table.getElementsByTagName('tr');
    var csv = [];
    
    // Capture the table headers
    var headers = table.getElementsByTagName('th');
    var headerData = [];
    for (var i = 0; i < headers.length; i++) {
        headerData.push(headers[i].innerText);
    }
    csv.push(headerData.join(','));  // Add headers to CSV

    // Loop through each row (excluding the header row)
    for (var i = 1; i < rows.length; i++) {  // Start from 1 to skip the header row
        var row = rows[i];
        var cols = row.getElementsByTagName('td');
        var rowData = [];
        
        // Loop through each column and add to rowData array
        for (var j = 0; j < cols.length; j++) {
            rowData.push(cols[j].innerText);
        }

        // Join the row data with commas and add to CSV array
        csv.push(rowData.join(','));
    }

    // Join the rows with new line and prepare CSV data
    var csvData = csv.join('\n');
    
    // Create a temporary link element
    var downloadLink = document.createElement('a');
    downloadLink.href = 'data:text/csv;charset=utf-8,' + encodeURI(csvData);
    downloadLink.target = '_blank';
    downloadLink.download = tableId + '.csv';

    // Trigger the download
    downloadLink.click();
}

</script>
