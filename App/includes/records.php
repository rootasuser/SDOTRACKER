<?php
require_once __DIR__ . '/../Config/Config.php';

use App\Config\Config;

$config = new Config();
$conn   = $config->DB_CONNECTION;

// ===> Fetch distinct positions, schools, and subjects in alphabetical order
$positions_sql = "SELECT DISTINCT empPosition FROM positions ORDER BY empPosition ASC";
$schools_sql = "SELECT DISTINCT empAssignSchool FROM schools ORDER BY empAssignSchool ASC";
$subjects_sql = "SELECT DISTINCT empTeachingSubject FROM subjects ORDER BY empTeachingSubject ASC";

$positions_stmt = $conn->prepare($positions_sql);
$positions_stmt->execute();
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

$schools_stmt = $conn->prepare($schools_sql);
$schools_stmt->execute();
$schools = $schools_stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects_stmt = $conn->prepare($subjects_sql);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

$employees = [];
$totalEmployees = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ===> Sanitize 
    $positionFilter = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $schoolFilter = filter_input(INPUT_POST, 'school', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $subjectFilter = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // New filters
    $tinNumFilter = filter_input(INPUT_POST, 'tinNum', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $plantillaFilter = filter_input(INPUT_POST, 'plantilla', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $districtFilter = filter_input(INPUT_POST, 'district', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $sexFilter = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $addressFilter = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $csFilter = filter_input(INPUT_POST, 'cs', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $dobFilter = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // ===> Query to get filtered employees
    $filter_sql = "
        SELECT DISTINCT e.id, e.empName, e.empNumber, empStatus, e.empDob, e.empCS, e.empTinNum, e.empPlantilla, e.empDistrict, e.empSex, e.empAddress, e.empHistory, 
               p.empPosition, sc.empAssignSchool, s.empTeachingSubject
        FROM employees e
        LEFT JOIN positions p ON e.empPosition_id = p.id
        LEFT JOIN schools sc ON e.empAssignSchool_id = sc.id
        LEFT JOIN employee_subjects es ON e.id = es.employee_id
        LEFT JOIN subjects s ON es.subject_id = s.id
        WHERE 1=1";

    $count_sql = "SELECT COUNT(DISTINCT e.id) AS total FROM employees e
        LEFT JOIN positions p ON e.empPosition_id = p.id
        LEFT JOIN schools sc ON e.empAssignSchool_id = sc.id
        LEFT JOIN employee_subjects es ON e.id = es.employee_id
        LEFT JOIN subjects s ON es.subject_id = s.id
        WHERE 1=1";

    $params = [];

    // ===> filters
    if (!empty($positionFilter)) {
        $filter_sql .= " AND p.empPosition = :position";
        $count_sql .= " AND p.empPosition = :position";
        $params[':position'] = $positionFilter;
    }
    if (!empty($schoolFilter)) {
        $filter_sql .= " AND sc.empAssignSchool = :school";
        $count_sql .= " AND sc.empAssignSchool = :school";
        $params[':school'] = $schoolFilter;
    }
    if (!empty($subjectFilter)) {
        $filter_sql .= " AND s.empTeachingSubject = :subject";
        $count_sql .= " AND s.empTeachingSubject = :subject";
        $params[':subject'] = $subjectFilter;
    }

    // New filter conditions
    if (!empty($tinNumFilter)) {
        $filter_sql .= " AND e.empTinNum = :tinNum";
        $count_sql .= " AND e.empTinNum = :tinNum";
        $params[':tinNum'] = $tinNumFilter;
    }
    if (!empty($plantillaFilter)) {
        $filter_sql .= " AND e.empPlantilla = :plantilla";
        $count_sql .= " AND e.empPlantilla = :plantilla";
        $params[':plantilla'] = $plantillaFilter;
    }
    if (!empty($districtFilter)) {
        $filter_sql .= " AND e.empDistrict = :district";
        $count_sql .= " AND e.empDistrict = :district";
        $params[':district'] = $districtFilter;
    }
    if (!empty($sexFilter)) {
        $filter_sql .= " AND e.empSex = :sex";
        $count_sql .= " AND e.empSex = :sex";
        $params[':sex'] = $sexFilter;
    }
    if (!empty($addressFilter)) {
        $filter_sql .= " AND e.empAddress = :address";
        $count_sql .= " AND e.empAddress = :address";
        $params[':address'] = $addressFilter;
    }
    if (!empty($csFilter)) {
        $filter_sql .= " AND e.empCS = :cs";
        $count_sql .= " AND e.empCS = :cs";
        $params[':cs'] = $csFilter;
    }
    if (!empty($dobFilter)) {
        $filter_sql .= " AND e.empDob = :dob";
        $count_sql .= " AND e.empDob = :dob";
        $params[':dob'] = $dobFilter;
    }

    // ===> Sort alphabetically
    $filter_sql .= " ORDER BY e.empName ASC";

    // ===> Exec. filtered emp query
    $filter_stmt = $conn->prepare($filter_sql);
    foreach ($params as $key => $value) {
        $filter_stmt->bindValue($key, $value);
    }
    $filter_stmt->execute();
    $employees = $filter_stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===> Remove duplicate emp
    $uniqueEmployees = [];
    foreach ($employees as $employee) {
        if (!isset($uniqueEmployees[$employee['id']])) {
            $uniqueEmployees[$employee['id']] = $employee;
        }
    }
    $employees = array_values($uniqueEmployees);

    // ===> Exec. count query
    $count_stmt = $conn->prepare($count_sql);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $totalEmployees = $count_result['total'] ?? 0;
}
?>

<style>
    label {
        color: #000;
    }
</style>

<div class="container mt-2">
    <div class="card">
        <div class="card-body">
           <!-- Search Form -->
<form method="POST" class="form mb-3">
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="position">Position</label>
            <select name="position" class="form-control" id="position">
                <option value="">Filter By Position</option>
                <?php foreach ($positions as $position): ?>
                    <option value="<?php echo htmlspecialchars($position['empPosition']); ?>"><?php echo htmlspecialchars($position['empPosition']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="school">School</label>
            <select name="school" class="form-control" id="school">
                <option value="">Filter By School</option>
                <?php foreach ($schools as $school): ?>
                    <option value="<?php echo htmlspecialchars($school['empAssignSchool']); ?>"><?php echo htmlspecialchars($school['empAssignSchool']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="subject">Teaching Subject</label>
            <select name="subject" class="form-control" id="subject">
                <option value="">Filter By Subject</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo htmlspecialchars($subject['empTeachingSubject']); ?>"><?php echo htmlspecialchars($subject['empTeachingSubject']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search</button>
        </div>
    </div>

    <!-- New filters row 1 -->
    <div class="form-row mt-3">
        <div class="form-group col-md-3">
            <label for="tinNum">Filter By TIN Number</label>
            <input type="text" name="tinNum" class="form-control" id="tinNum">
        </div>
        <div class="form-group col-md-3">
            <label for="plantilla">Filter By Plantilla #</label>
            <input type="text" name="plantilla" class="form-control" id="plantilla">
        </div>
        <div class="form-group col-md-3">
            <label for="district">Filter By District</label>
            <input type="text" name="district" class="form-control" id="district">
        </div>
        <div class="form-group col-md-3">
            <label for="sex">Sex</label>
            <select name="sex" class="form-control" id="sex">
                <option value="">Filter By Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
    </div>

    <!-- New filters row 2 -->
    <div class="form-row mt-3">
        <div class="form-group col-md-3">
            <label for="address">Address</label>
            <input type="text" name="address" class="form-control" id="address">
        </div>
        <div class="form-group col-md-3">
            <label for="cs">Civil Status</label>
            <input type="text" name="cs" class="form-control" id="cs">
        </div>
        <div class="form-group col-md-3">
            <label for="dob">Date of Birth</label>
            <input type="date" name="dob" class="form-control" id="dob">
        </div>
        <div class="form-group col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search</button>
        </div>
    </div>
</form>

            <!-- Print and Export Buttons -->
            <div class="mb-3">
                <button onclick="printTable('employeeTable')" class="btn btn-light" style="color: #fff; background-color: #000;"><i class="fas fa-print"></i> Print</button>
                <button onclick="exportToCSV('employeeTable')" class="btn btn-light" style="color: #fff; background-color: #000;"><i class="fas fa-file-export"></i> Export to CSV</button>
            </div>

            <?php
                // **** Switching message ****
                if ($totalEmployees == 0 || $totalEmployees == 1) 
                {
                    $message = " Employee.";
                } else {
                    $message = " Employees.";
                }
            
            ?>

    <style>
        th, td  {
            color: #000;
        }
    </style>
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


document.addEventListener("DOMContentLoaded", function() {
    // Search
    function searchThisEmpInTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInputEmployee");
        if (!input) return; // Check if input exists

        filter = input.value.toLowerCase();
        table = document.getElementById("employeeTable");
        if (!table) return; // Check if table exists

        tr = table.querySelectorAll("tbody tr");

        for (i = 0; i < tr.length; i++) {
            td = tr[i].querySelector("td:nth-child(2)"); 
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    // Add event listener to the search input
    var searchInput = document.getElementById("searchInputEmployee");
    if (searchInput) {
        searchInput.addEventListener("keyup", searchThisEmpInTable);
    }
});
</script>


<div class="d-flex justify-content-end align-items-end mt-2 mb-2">
    <input type="text" id="searchInputEmployee" placeholder="Search employee..." class="form-control w-25">
</div>

            <p><span class="badge badge-pill bg-danger text-white"><?= $totalEmployees ?></span> <?php echo $message; ?></p>

            <!-- Display Employees -->
            <?php if ($employees): ?>
                <div class="table-responsive">
                <table class="table" id="employeeTable" style="overflow-y: auto;">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th style="white-space: nowrap;">Name</th>
                            <th style="white-space: nowrap;">Employee Number</th>
                            <th style="white-space: nowrap;">Date of Birth</th>
                            <th style="white-space: nowrap;">Civil Status</th>
                            <th style="white-space: nowrap;">Tin #</th>
                            <th style="white-space: nowrap;">Plantilla #</th>
                            <th style="white-space: nowrap;">District</th>
                            <th style="white-space: nowrap;">Status</th>
                            <th style="white-space: nowrap;">Position</th>
                            <th style="white-space: nowrap;">School</th>
                            <th style="white-space: nowrap;">Subject</th>
                            <th style="white-space: nowrap;">Sex</th>
                            <th style="white-space: nowrap;">Address</th>
                            <th style="white-space: nowrap;">History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td style="display: none"><?php echo htmlspecialchars($employee['id']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars_decode(($employee['empName'])); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empNumber']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empDob']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empCS']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empTinNum']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empPlantilla']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empDistrict']); ?></td>
                                <td style="white-space: nowrap;"><span class="badge badge-pill bg-primary text-white"><?php echo htmlspecialchars($employee['empStatus']); ?></span></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empPosition']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empAssignSchool']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empTeachingSubject']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empSex']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empAddress']); ?></td>
                                <td style="white-space: nowrap;"><?php echo htmlspecialchars($employee['empHistory']); ?></td>
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
</div>


<script>

function printTable(tableId) {
    var tableContent = document.getElementById(tableId).outerHTML;

    var styles = `
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th, td {
                border: 1px solid black; 
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


function exportToCSV(tableId) {
    var table = document.getElementById(tableId);
    var rows = table.getElementsByTagName('tr');
    var csv = [];
    
    var headers = table.getElementsByTagName('th');
    var headerData = [];
    for (var i = 0; i < headers.length; i++) {
        headerData.push(headers[i].innerText);
    }
    csv.push(headerData.join(',')); 

    for (var i = 1; i < rows.length; i++) {  
        var row = rows[i];
        var cols = row.getElementsByTagName('td');
        var rowData = [];
        
        for (var j = 0; j < cols.length; j++) {
            rowData.push(cols[j].innerText);
        }

        csv.push(rowData.join(','));
    }


    var csvData = csv.join('\n');
    
    var downloadLink = document.createElement('a');
    downloadLink.href = 'data:text/csv;charset=utf-8,' + encodeURI(csvData);
    downloadLink.target = '_blank';
    downloadLink.download = tableId + '.csv';

    downloadLink.click();
}


</script>
