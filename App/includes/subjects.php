<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/Config.php';
use App\Config\Config;

try {
    $config = new Config();
    $conn = $config->DB_CONNECTION;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection error.");
}

function addSubject($conn, $subject_name) {
    try {
        $stmt = $conn->prepare("INSERT INTO subjects (empTeachingSubject) VALUES (?)");
        $stmt->execute([strip_tags($subject_name)]);
        return true;
    } catch (PDOException $e) {
        error_log("Add subject error: " . $e->getMessage());
        return false;
    }
}

function deleteSubject($conn, $delete_id) {
    try {
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$delete_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Delete subject error: " . $e->getMessage());
        return false;
    }
}

function getSubjects($conn) {
    try {
        return $conn->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch subjects error: " . $e->getMessage());
        return [];
    }
}

function getEmployeesBySubject($conn, $subject_id) {
    try {
        $stmt = $conn->prepare("
            SELECT e.* 
            FROM employees e
            JOIN employee_subjects es ON e.id = es.employee_id
            WHERE es.subject_id = ?");
        $stmt->execute([$subject_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch employees error: " . $e->getMessage());
        return [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject']) && isset($_POST['empTeachingSubject'])) {
        $subject_name = $_POST['empTeachingSubject'];
        $success = addSubject($conn, $subject_name);
        if ($success) {
            $_SESSION['message'] = 'Subject added successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to add subject.';
            $_SESSION['message_type'] = 'danger';
        }
    }
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $success = deleteSubject($conn, $delete_id);
        if ($success) {
            $_SESSION['message'] = 'Subject deleted successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to delete subject.';
            $_SESSION['message_type'] = 'danger';
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
  <title>Subjects</title>
  <link rel="stylesheet" href="/sdotracker/node_modules/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>
<?php if (isset($_SESSION['message'])): ?>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
    <div class="toast align-items-center text-white bg-<?= $_SESSION['message_type'] === 'success' ? 'success' : 'danger' ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
      </div>
    </div>
  </div>
  <!-- Hide the toast after a delay -->
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

<?php
if (isset($_GET['subject_id'])):
    $subject_id = intval($_GET['subject_id']);
    $stmtSubject = $conn->prepare("SELECT empTeachingSubject FROM subjects WHERE id = ?");
    $stmtSubject->execute([$subject_id]);
    $subject = $stmtSubject->fetch(PDO::FETCH_ASSOC);
    ?>
 <div class="container mt-3">
    <h2>Employees Teaching Subject: <?= htmlspecialchars($subject['empTeachingSubject']) ?></h2>
    <a href="dashboard?page=subjects" class="btn btn-secondary mb-3">Back to Subjects</a>
    <?php
    $employees = getEmployeesBySubject($conn, $subject_id);
    if ($employees):
    ?>
    <style>
         .table-container {
        overflow-x: auto;
        cursor: pointer;
    }
    </style>
    <div class="d-flex justify-content-end align-items-end mt-2 mb-2">
        <input type="text" id="searchInputEmp" placeholder="Search employee..." class="form-control w-25">
    </div>
    <div class="table-container table-responsive" id="scrollableTable">
        <table class="table table-bordered" id="searchEmp">
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
                        <td class="text-nowrap"><?= htmlspecialchars($emp['empCS']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($emp['empTinNum']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($emp['empPlantilla']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($emp['empDistrict']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($emp['empAddress']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($emp['empSex']) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($emp['empHistory']) ?></td>
                        <td class="text-nowrap">
                        <span class="badge rounded-pill bg-primary badge-sm"><?= htmlspecialchars($emp['empStatus']) ?></span>
                        </td>
                        <td class="text-nowrap"><?= htmlspecialchars($emp['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p>No employees found for this subject.</p>
    <?php endif; ?>
</div>


<?php else: ?>
   
    <div class="container mt-3 bg-white">
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                <input type="text" id="searchInput" placeholder="Search subject..." class="form-control w-25">
                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="fa fa-plus"></i> Add Subject
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="subjectsTable" class="table">
                        <thead>
                            <tr>
                                <th style="display: none;" class="text-nowrap">ID</th>
                                <th class="text-nowrap">Subject Name</th>
                                <th class="text-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subjects = getSubjects($conn);
                            foreach ($subjects as $row):
                            ?>
                                <tr>
                                    <td style="display: none;" class="text-nowrap"><?= htmlspecialchars($row['id']) ?></td>
                                    <td class="text-nowrap">
                                        <a href="dashboard?page=subjects&subject_id=<?= htmlspecialchars($row['id']) ?>">
                                            <?= htmlspecialchars($row['empTeachingSubject']) ?>
                                        </a>
                                    </td>
                                    <td class="text-nowrap">
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

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addSubjectModalLabel">Add New Subject</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST" action="">
              <div class="mb-3">
                <label for="empTeachingSubject" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="empTeachingSubject" name="empTeachingSubject" required>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="add_subject" class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
<?php endif; ?>

<script src="/sdotracker/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('keyup', function () {
        let filters = this.value.toLowerCase();
        let rows = document.querySelectorAll('#subjectsTable tbody tr');
        rows.forEach(row => {
            let teachingSubject = row.cells[1].textContent.toLowerCase();
            row.style.display = teachingSubject.includes(filters) ? '' : 'none';
        });
    });
}

var searchInputEmp = document.getElementById('searchInputEmp');
if (searchInputEmp) {
    searchInputEmp.addEventListener('keyup', function () {
        let filtering = this.value.toLowerCase();
        let rowsEmp = document.querySelectorAll('#searchEmp tbody tr');
        rowsEmp.forEach(row => {
            let teachingSubject = row.cells[1].textContent.toLowerCase();
            row.style.display = teachingSubject.includes(filtering) ? '' : 'none';
        });
    });
}


document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.getElementById('scrollableTable');

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

    tableContainer.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: false });

    function handleSwipe() {
        const difference = touchStartX - touchEndX;
        if (difference > 50) { 
            tableContainer.scrollLeft += 100;
        } else if (difference < -50) { 
            tableContainer.scrollLeft -= 100;
        }
    }
});
</script>
</body>
</html>
