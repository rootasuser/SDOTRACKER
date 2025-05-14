<?php

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Config/Config.php';

use App\Config\Config; 

$config = new Config();
$conn   = $config->DB_CONNECTION;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM schools WHERE id = ?");
        $stmt->bindParam(1, $delete_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'School deleted successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to delete school';
            $_SESSION['message_type'] = 'danger';
        }
    } elseif (isset($_POST['add_schools'])) {
        $school_name = trim($_POST['empAssignSchool']);
        if (!empty($school_name)) {
            $stmt = $conn->prepare("INSERT INTO schools (empAssignSchool) VALUES (?)");
            $stmt->bindParam(1, $school_name);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'School added successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to add school';
                $_SESSION['message_type'] = 'danger';
            }
        }
    }
}
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schools</title>
    <link rel="stylesheet" href="/sdotracker/node_modules/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>
<?php if (isset($_SESSION['message'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <div class="toast align-items-center text-white bg-<?= $_SESSION['message_type'] == 'success' ? 'success' : 'danger' ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                document.querySelector('.toast').classList.remove('show');
            }, 3000);
        });
    </script>
    <?php 
        unset($_SESSION['message']); 
        unset($_SESSION['message_type']);
    ?>
<?php endif; ?>

<script>
    // SEARCH ONKEYUP 
function searchThisEmp() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInputEmps");
    filter = input.value.toLowerCase();
    table = document.getElementById("searchEmpSchool");
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


   // MOVE LEFT - RIGHT CURSOR/SWIPE
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.getElementById('scrollableTable');
    if (tableContainer) {
        let isDown = false;
        let startX;
        let scrollLeft;

        tableContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - tableContainer.offsetLeft;
            scrollLeft = tableContainer.scrollLeft;
        });

        tableContainer.addEventListener('mouseleave', () => {
            isDown = false;
        });

        tableContainer.addEventListener('mouseup', () => {
            isDown = false;
        });

        tableContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - tableContainer.offsetLeft;
            const walk = (x - startX) * 2; 
            tableContainer.scrollLeft = scrollLeft - walk;
        });

        let touchStartX = 0;
        let touchEndX = 0;

        tableContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: false });

        tableContainer.addEventListener('touchmove', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const difference = touchStartX - touchEndX;
            if (Math.abs(difference) > 50) {
                e.preventDefault();
                tableContainer.scrollLeft += difference;
                touchStartX = touchEndX;
            }
        }, { passive: false });

        tableContainer.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const difference = touchStartX - touchEndX;
            if (difference > 50) { 
                tableContainer.scrollLeft += 100;
            } else if (difference < -50) { 
                tableContainer.scrollLeft -= 100;
            }
        }, { passive: false });
    } else {
        console.error("Element with ID 'scrollableTable' not found.");
    }
});
</script>

<?php
if (isset($_GET['school_id'])):
    $school_id = intval($_GET['school_id']);
    $stmtSchool = $conn->prepare("SELECT empAssignSchool FROM schools WHERE id = ?");
    $stmtSchool->execute([$school_id]);
    $school = $stmtSchool->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="container mt-3">
        <h2>Employees for School: <?= htmlspecialchars($school['empAssignSchool']) ?></h2>
        <a href="dashboard?page=schools" class="btn btn-secondary mb-3">Back to Schools</a>
        <?php
        $stmt = $conn->prepare("
            SELECT 
                e.id,
                e.empName,
                e.empNumber,
                e.empDob,
                e.empCS,
                e.empAddress,
                e.empSex,
                p.empPosition AS position,
                e.empHistory,
                e.empStatus,
                e.created_at
            FROM employees AS e
            INNER JOIN positions AS p ON e.empPosition_id = p.id
            WHERE e.empAssignSchool_id = ?
        ");
        $stmt->execute([$school_id]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($employees):
        ?>
        <style>
        .table-container {
        overflow-x: auto;
        cursor: pointer;
    }
        </style>
           <div class="d-flex justify-content-end align-items-end mt-2 mb-2">
      <input type="text" id="searchInputEmps" placeholder="Search employee..." class="form-control w-25" onkeyup="searchThisEmp()">
</div>
        <div class="table-container table-responsive" id="scrollableTable">
            <table class="table" id="searchEmpSchool">
                <thead>
                    <tr>
                        <th class="text-nowrap">#</th>
                        <th class="text-nowrap">Name</th>
                        <th class="text-nowrap">Employee Number</th>
                        <th class="text-nowrap">Date of Birth</th>
                        <th class="text-nowrap">Civil Status</th>
                        <th class="text-nowrap">Tin #</th>
                        <th class="text-nowrap">Plantilla #</th>
                        <th class="text-nowrap">District</th>
                        <th class="text-nowrap">Address</th>
                        <th class="text-nowrap">Sex</th>
                        <th class="text-nowrap">Position</th>
                        <th class="text-nowrap">History</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['id']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empName']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empNumber']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empDob']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empTinNum']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empPlantilla']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empDistrict']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empCS']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empAddress']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empSex']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['position']) ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['empHistory']) ?></td>
                            <td class="text-nowrap">
                                <span class="badge rounded-pill bg-primary badge-sm">
                                <?= htmlspecialchars($emp['empStatus']) ?>  
                                </span>
                            </td>
                            <td class="text-nowrap"><?= htmlspecialchars($emp['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No employees found for this school.</p>
        <?php endif; ?>
    </div>
    </div>
<?php else: ?>
    <!-- Display schools list -->
    <div class="container mt-3 bg-white">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <input type="text" id="searchInput" placeholder="Search schools..." class="form-control w-25">
                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addSchoolsModal">
                        <i class="fa fa-plus"></i> Add School
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="schoolsTable" class="table">
                        <thead>
                            <tr>
                                <th style="display: none;">ID</th>
                                <th>School Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM schools";
                            $result = $conn->query($sql);
                            foreach ($result as $row):
                            ?>
                                <tr>
                                    <td style="display: none;"><?= htmlspecialchars($row['id']) ?></td>
                                    <td>
                                        <a href="dashboard?page=schools&school_id=<?= htmlspecialchars($row['id']) ?>" class="school-link">
                                            <?= htmlspecialchars($row['empAssignSchool']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add School Modal for adding new schools -->
    <div class="modal fade" id="addSchoolsModal" tabindex="-1" aria-labelledby="addSchoolsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSchoolsModalLabel">Add New School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="empAssignSchool" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="empAssignSchool" name="empAssignSchool" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_schools" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/sdotracker/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#schoolsTable tbody tr');
            rows.forEach(row => {
                let schoolName = row.cells[1].textContent.toLowerCase();
                row.style.display = schoolName.includes(filter) ? '' : 'none';
            });
        });
    </script>
<?php endif; ?>
</body>
</html>
