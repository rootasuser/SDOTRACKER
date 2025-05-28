<?php
require_once '../../Config/Config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,
        'use_strict_mode' => true,
        'use_cookies' => true,
        'use_only_cookies' => true
    ]);
}


// Regenerate session ID for security
if (!isset($_SESSION['user']) || session_id() == '') {
    session_regenerate_id(true);
}

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit;
}


// Get username from session or use fallback
$username = $_SESSION['username'] ?? 'Unknown User';

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    $positionsQuery = "SELECT id, empPosition FROM positions";
    $positionsResult = $conn->query($positionsQuery);

    $schoolsQuery = "SELECT id, empAssignSchool FROM schools";
    $schoolsResult = $conn->query($schoolsQuery);

    $subjectsQuery = "SELECT id, empTeachingSubject FROM subjects";
    $subjectsResult = $conn->query($subjectsQuery);

    function getPositionName($conn, $positionId) {
        $stmt = $conn->prepare("SELECT empPosition FROM positions WHERE id = :id");
        $stmt->execute([':id' => $positionId]);
        return $stmt->fetchColumn() ?: $positionId;
    }

    function getSchoolName($conn, $schoolId) {
        $stmt = $conn->prepare("SELECT empAssignSchool FROM schools WHERE id = :id");
        $stmt->execute([':id' => $schoolId]);
        return $stmt->fetchColumn() ?: $schoolId;
    }
    
    $employee = null;
    $error = '';
    $successMessage = ''; 
    $errorMessage = ''; 

    

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
        $searchTerm = trim($_POST['search']);

        try {

            $stmt = $conn->prepare("
                SELECT e.*, 
                       s.empAssignSchool, 
                       p.empPosition, 
                       GROUP_CONCAT(sub.id) AS subjectIds,
                       GROUP_CONCAT(sub.empTeachingSubject SEPARATOR ', ') AS subjectNames
                FROM employees e
                LEFT JOIN schools s ON e.empAssignSchool_id = s.id
                LEFT JOIN positions p ON e.empPosition_id = p.id
                LEFT JOIN employee_subjects es ON e.id = es.employee_id
                LEFT JOIN subjects sub ON es.subject_id = sub.id
                WHERE e.empName LIKE :search OR e.empNumber LIKE :search
                GROUP BY e.id
                LIMIT 1
            ");
            $stmt->execute([':search' => "%$searchTerm%"]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                $error = "No employee found.";
            }
        } catch (PDOException $e) {
            $error = "DB error: " . $e->getMessage();
        }
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empId'])) {
        try {
            // Get existing employee data before update
            $oldStmt = $conn->prepare("SELECT * FROM employees WHERE id = :empId");
            $oldStmt->execute([':empId' => $_POST['empId']]);
            $oldEmployee = $oldStmt->fetch(PDO::FETCH_ASSOC);

            // Update specific employee details
            $updateStmt = $conn->prepare("
                UPDATE employees 
                SET empName = :empName, 
                    empNumber = :empNumber,
                    empDob = :empDob, 
                    empCS = :empCS,
                    empTinNum = :empTinNum,
                    empPlantilla = :empPlantilla,
                    empDistrict = :empDistrict,
                    empAddress = :empAddress, 
                    empSex = :empSex, 
                    empPosition_id = :empPosition, 
                    empAssignSchool_id = :empAssignSchool, 
                    empHistory = :empHistory,
                    empStatus = :empStatus
                WHERE id = :empId
            ");
        
            $updateStmt->execute([
                ':empName'         => $_POST['empName'],
                ':empNumber'       => $_POST['empNumber'],
                ':empDob'          => $_POST['empDob'],
                ':empCS'           => $_POST['empCS'],
                ':empTinNum'       => $_POST['empTinNum'],
                ':empPlantilla'    => $_POST['empPlantilla'],
                ':empDistrict'     => $_POST['empDistrict'],
                ':empAddress'      => $_POST['empAddress'],
                ':empSex'          => $_POST['empSex'],
                ':empPosition'     => $_POST['empPosition'],     
                ':empAssignSchool' => $_POST['empAssignSchool'],  
                ':empHistory'      => $_POST['empHistory'],
                ':empStatus'       => $_POST['empStatus'],
                ':empId'           => $_POST['empId']
            ]);
        
            // Log changes upon updating
            $username = $_SESSION['username'] ?? 'Unknown User';
            $employeeId = $_POST['empId'];
            $fieldsToLog = [
                'empName' => 'Name',
                'empNumber' => 'Employee Number',
                'empDob' => 'Date of Birth',
                'empCS' => 'Civil Status',
                'empTinNum' => 'TIN Number',
                'empPlantilla' => 'Plantilla',
                'empDistrict' => 'District',
                'empAddress' => 'Address',
                'empSex' => 'Sex',
                'empPosition_id' => 'Position',
                'empAssignSchool_id' => 'Assigned School',
                'empHistory' => 'History',
                'empStatus' => 'Status'
            ];

            foreach ($fieldsToLog as $dbField => $fieldName) {
                $oldValue = $oldEmployee[$dbField] ?? '';
                $newValue = $_POST[$dbField] ?? '';

                if ($fieldName === 'Position') {
                    $oldValue = getPositionName($conn, $oldValue);
                    $newValue = getPositionName($conn, $newValue);
                } elseif ($fieldName === 'Assigned School') {
                    $oldValue = getSchoolName($conn, $oldValue);
                    $newValue = getSchoolName($conn, $newValue);
                }
                # Insert now the specific data based on what u updated
                if ($oldValue != $newValue) {
                    $insertLogStmt = $conn->prepare("
                        INSERT INTO admin_updated_activity_logs (
                            username, employee_id, field_name, old_value, new_value
                        ) VALUES (
                            :username, :employeeId, :fieldName, :oldValue, :newValue
                        )
                    ");
                    $insertLogStmt->execute([
                        ':username' => $username,
                        ':employeeId' => $employeeId,
                        ':fieldName' => $fieldName,
                        ':oldValue' => $oldValue,
                        ':newValue' => $newValue
                    ]);
                }
            }

            $successMessage = "Employee details successfully updated.";
        } catch (PDOException $e) {
            $errorMessage = "Update failed: " . $e->getMessage();
        }       
    }

    $employeeSubjects = ($employee && !empty($employee['subjectIds']))
                        ? explode(',', $employee['subjectIds'])
                        : [];
} catch (PDOException $e) {
    $error = "DB err: " . $e->getMessage();
}

$initials = isset($employee['empName']) ? strtoupper(substr($employee['empName'], 0, 2)) : 'NA';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Employee</title>
  <link rel="stylesheet" href="./../../node_modules/bootstrap/dist/css/bootstrap.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

     <!-- Toast Container (Top-Right Position) -->
     <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
    <?php if (!empty($error)): ?>
        <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($error) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= $successMessage; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <?= $errorMessage; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Trigger Toasts for updating emp info-->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let toastElements = document.querySelectorAll(".toast");
        toastElements.forEach(function (toastEl) {
            let toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        });
    });
</script>

<div class="container mb-3">
    <div class="mb-3">
        <h1 class="text-dark">Search / Update Employee <i class="fas fa-arrow-alt-circle-right text-danger"></i> <?= htmlspecialchars($employee['empName'] ?? ''); ?></h1>
        <form action="" method="POST" class="position-relative">
    <div class="input-group w-50">
        <input type="text" class="form-control" name="search" 
               value="<?= htmlspecialchars($_POST['search'] ?? '') ?>" 
               placeholder="Search by name or number" required>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    </div>
</form>
<div class="suggestions-container" id="suggestions"></div>
<script>
    // Search with Suggestions & click suggested data
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const form = searchInput.closest('form');
    
    // Create suggestion container
    const suggestionContainer = document.createElement('div');
    suggestionContainer.className = 'suggestions-container';
    suggestionContainer.style.backgroundColor = '#fff';
    suggestionContainer.style.display = 'none';
    suggestionContainer.style.position = 'absolute';
    suggestionContainer.style.width = 'calc(100% - 2px)';
    suggestionContainer.style.zIndex = '1000';
    suggestionContainer.style.border = '1px solid #ccc';
    suggestionContainer.style.cursor = 'pointer';
    const style = document.createElement('style');
        style.textContent = `
            .suggestion-item {
                padding: 8px 12px;
                cursor: pointer;
            }

            .suggestion-item:hover {
                background-color: #e6f7ff; 
                color: #0066cc;
            }
        `;
document.head.appendChild(style);
    form.appendChild(suggestionContainer);

    searchInput.addEventListener('input', function() {
        const term = this.value.trim();
        
        // if (term.length < 1) {
        //     suggestionContainer.style.display = 'none';
        //     return;
        // }
        
        fetch(`search_suggestions.php?term=${encodeURIComponent(term)}`)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        suggestionContainer.innerHTML = '';
        suggestionContainer.style.display = 'block';
        
        if (data.length > 0) {
            data.forEach(employee => {
                const suggestion = document.createElement('div');
                suggestion.className = 'suggestion-item';
                suggestion.textContent = `${employee.empName} (${employee.empNumber})`;
                suggestion.addEventListener('click', () => {
                    searchInput.value = employee.empName;
                    form.submit();
                });
                suggestionContainer.appendChild(suggestion);
            });
        } else {
            const msg = document.createElement('div');
            msg.className = 'suggestion-item';
            msg.textContent = 'No results found';
            msg.style.color = '#999';
            suggestionContainer.appendChild(msg);
        }
    })
    .catch(error => {
        console.error('Error fetching suggestions:', error);
        suggestionContainer.innerHTML = '<div class="suggestion-item" style="color: #999;">No results found</div>';
    });
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!form.contains(e.target)) {
            suggestionContainer.style.display = 'none';
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            suggestionContainer.style.display = 'none';
        }
    });
});
</script>


    </div>

    <?php if ($employee): ?>
        <div class="container mt-2  ">
    <div class="card mx-auto shadow" style="max-width: 600px;">
        <div class="card-body text-center">
            <!-- Profile Image (Initials) -->
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                style="width: 100px; height: 100px; font-size: 32px; font-weight: bold;">
                <?= $initials ?>
            </div>

            <!-- Employee Name & Position -->
            <h4 class="mt-3"><?= htmlspecialchars_decode(($employee['empName'] ?? 'N/A')) ?></h4>
            <p class="text-muted"><?= htmlspecialchars($employee['empPosition'] ?? 'N/A') ?></p>

            <!-- Employee Details -->
            <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush text-start">
                    <li class="list-group-item"><strong>Employee #:</strong> <?= htmlspecialchars($employee['empNumber'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>Date of Birth:</strong> <?= htmlspecialchars($employee['empDob'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>Civil Status:</strong> <?= htmlspecialchars($employee['empCS'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>Tin #:</strong> <?= htmlspecialchars($employee['empTinNum'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>Plantilla #:</strong> <?= htmlspecialchars($employee['empPlantilla'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>District:</strong> <?= htmlspecialchars($employee['empDistrict'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>Address:</strong> <?= htmlspecialchars($employee['empAddress'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>Status:</strong> <span class="badge badge-pill bg-danger text-white"><?= htmlspecialchars($employee['empStatus'] ?? 'N/A') ?></span></li>
                    <li class="list-group-item"><strong>Sex:</strong> <?= htmlspecialchars($employee['empSex'] ?? 'N/A') ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush text-start">
                    <li class="list-group-item"><strong>School Assigned:</strong> <?= htmlspecialchars($employee['empAssignSchool'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>Teaching Subject/s:</strong> <?= htmlspecialchars($employee['subjectNames'] ?? 'N/A') ?></li>
                    <li class="list-group-item"><strong>School History:</strong> <?= htmlspecialchars($employee['empHistory'] ?? 'N/A') ?></li>
                </ul>
            </div>
        </div>
            <div class="mb-2 mt-2 align-items-end justify-content-end d-flex">
                <button class="btn btn-outline-primary" id="btnModal"><i class="fas fa-edit"></i></button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-container {
  display: none; 
  position: fixed; 
  z-index: 1; 
  padding-top: 100px; 
  left: 0;
  top: 0;
  width: 100%; 
  height: 100%; 
  overflow: auto; 
  background-color: rgb(0,0,0); 
  background-color: rgba(0,0,0,0.4); 
}
.modal-content-update {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 80%;
}

.close {
  color: red;
  float: right;
  font-size: 38px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}

input[type="radio"] {
  appearance: none;
  width: 20px;
  height: 20px;
  border: 2px solid #0FFF50;
  border-radius: 50%; 
  display: inline-block;
  position: relative;
  cursor: pointer;
}

input[type="radio"]:checked::before {
  content: "✔"; 
  font-size: 16px;
  font-weight: bold;
  color: blue;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
input[type="checkbox"] {
        transform: scale(1.5);
        accent-color: #0FFF50;
    }
</style>


<!-- Modal of Update -->
<div id="updateModal" class="modal-container">

  <!-- Modal content -->
  <div class="modal-content-update">
    <span class="close">&times;</span>
    <div class="card mt-4" id="updateCard">
            <div class="card-header text-start" style="background-color: #20263e; color: #ffffff; font-size: 20px; font-weight: bolder;">
                Update Employee <?= htmlspecialchars($employee['empName'] ?? 'N/A') ?> Information
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="empId" value="<?= htmlspecialchars($employee['id']) ?>">
                    <input type="hidden" name="username" value="<?= htmlspecialchars($username ?? '') ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">Update Employee Name</label>
                            <input type="text" class="form-control" name="empName" 
                                   value="<?= htmlspecialchars($employee['empName']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">Update Employee Number</label>
                            <input type="number" class="form-control" name="empNumber" 
                                   value="<?= htmlspecialchars($employee['empNumber']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">Update Date of Birth</label>
                            <input type="date" class="form-control" name="empDob" 
                                   value="<?= htmlspecialchars($employee['empDob']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">Update Civil Status</label>
                            <select name="empCS" id="empCS" class="form-control">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">Tin #</label>
                            <input type="text" class="form-control" name="empTinNum" 
                                   value="<?= htmlspecialchars($employee['empTinNum']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">Plantilla #</label>
                            <input type="text" class="form-control" name="empPlantilla" 
                                   value="<?= htmlspecialchars($employee['empPlantilla']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">District</label>
                            <input type="text" class="form-control" name="empDistrict" 
                                   value="<?= htmlspecialchars($employee['empDistrict']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;">Update Address</label>
                            <input type="text" class="form-control" name="empAddress" 
                                   value="<?= htmlspecialchars($employee['empAddress']) ?>" required>
                        </div>

                        <div class="col-md-4">
                        <label class="form-label" style="color: #000;">Update Employee Status</label>
                        <select class="form-control" name="empStatus" required>
                            <option value="Active" <?= isset($employee['empStatus']) && $employee['empStatus'] == 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= isset($employee['empStatus']) && $employee['empStatus'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="On Leave" <?= isset($employee['empStatus']) && $employee['empStatus'] == 'On Leave' ? 'selected' : '' ?>>On Leave</option>
                            <option value="Retired" <?= isset($employee['empStatus']) && $employee['empStatus'] == 'Retired' ? 'selected' : ''?>>Retired</option>
                            <option value="Terminated" <?= isset($employee['empStatus']) && $employee['empStatus'] == 'Terminated' ? 'selected' : '' ?>>Terminated</option>
                        </select>
                    </div>
                    </div>


                    <div class="row mt-3">
                        <!-- Update Sex -->
                        <div class="col-md-4">
                            <label class="form-label" style="color: #000;"><span style="color: red;">*</span>Select to update sex</label>
                            <div>
                                <label>
                                    <input type="radio" name="empSex" value="Male" <?= ($employee['empSex'] === 'Male') ? 'checked' : '' ?>> Male
                                </label>
                                <label class="ms-3">
                                    <input type="radio" name="empSex" value="Female" <?= ($employee['empSex'] === 'Female') ? 'checked' : '' ?>> Female
                                </label>
                            </div>
                        </div>

                        <!-- Update Position -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <!-- <label for="searchPositionRadioButton" class="form-label">Search Position</label> -->
                                <input type="text" id="searchPositionRadioButton" class="form-control" onkeyup="searchRadioButtonPosition()" placeholder="Search Position" />
                            </div>

                            <label class="form-label" style="color: #000;">
                                <span style="color: red;">*</span> Select to update position
                            </label>

                            <div style="max-height: 200px; overflow-y: auto;" id="empPositionsRadioButtons">
                                <?php foreach ($positionsResult as $position): ?>
                                    <div class="form-check">  <!-- ✅ Each radio button inside a container -->
                                        <input type="radio" class="form-check-input" name="empPosition" value="<?= htmlspecialchars($position['id']) ?>"
                                            <?= ($position['id'] == $employee['empPosition_id']) ? 'checked' : '' ?>>
                                        <label class="form-check-label text-dark">
                                            <?= htmlspecialchars($position['empPosition']) ?>
                                        </label>
                                        <hr>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                            
                     
                <!-- Update School -->
                <div class="col-md-4">
                    <div class="mb-3">
                        <!-- <label for="searchAssignSchoolRadioButton" class="form-label">Search Assign School</label> -->
                        <input type="text" id="searchAssignSchoolRadioButton" class="form-control" onkeyup="searchRadioButtonSchool()" placeholder="Search School" />
                    </div>

                    <label class="form-label" style="color: #000;">
                        <span style="color: red;">*</span> Select to update assigned school
                    </label>

                    <div style="max-height: 200px; overflow-y: auto;" id="empAssignSchoolRadioButtons">
                        <?php foreach ($schoolsResult as $school): ?>
                            <div class="form-check">  <!-- ✅ Each radio button inside a container -->
                                <input type="radio" class="form-check-input" name="empAssignSchool" value="<?= htmlspecialchars($school['id']) ?>"
                                    <?= ($school['id'] == $employee['empAssignSchool_id']) ? 'checked' : '' ?>>
                                <label class="form-check-label text-dark">
                                    <?= htmlspecialchars($school['empAssignSchool']) ?>
                                </label>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
        </div>

                    <div class="mb-3">
                        <!-- <label for="searchTeachingSubjects" class="form-label">Search Teaching Subject/s</label> -->
                        <input type="text" id="searchTeachingSubjects" class="form-control w-25" onkeyup="searchCheckboxes()" placeholder="Search Teaching Subject" />
                    </div>

                    <!-- Update Subjects and History -->
                    <label class="form-label" style="color: #000;"><span style="color: red;">*</span>Select to update teaching subject/s</label>
                    <div class="row mt-3">
                        <!-- Update Subjects -->
                        <div class="col-md-4" style="max-height: 200px; overflow-y: auto;">
                            <div id="empTeachingSubjectCheckboxes">
                                <?php foreach ($subjectsResult as $subject): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="empTeachingSubject[]" 
                                               value="<?= htmlspecialchars($subject['id']) ?>"
                                               <?= in_array($subject['id'], $employeeSubjects) ? 'checked' : '' ?>>
                                        <label class="form-check-label text-dark">
                                            <?= htmlspecialchars($subject['empTeachingSubject']) ?>
                                            <hr>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Update History -->
                        <div class="col-md-8">
                            <label class="form-label" style="color: #000;">School History</label>
                            <textarea class="form-control" name="empHistory" rows="5" required><?= htmlspecialchars($employee['empHistory']) ?></textarea>
                        </div>
                    </div>

                    
                    <div class="mt-2 d-flex justify-content-end align-items-end">
                    <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                   
                </form>


    <hr>

                        <!-- TRAINING SECTION -->
                    <div class="container mt-1 mb-1" id="trainingContainer">
                        <h3 class="text-end" style="color: #000;">
                            Training Attended
                        </h3>
                        
                        <div class="container">
                            <form action="" method="POST" id="searchTrainingForm">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="title" id="searchTraining" placeholder="Search training title" />
                                </div>
                            </form>
                            <div id="trainingSuggestions"></div>
                        </div>

                        <div class="table-responsive">
                                    <!-- Trainings Linked to Employee -->
                                <div class="mt-4">
                                    <h5 style="color: #000;">Trainings Linked to This Employee</h5>
                                    <div class="table-responsive" id="employeeTrainingsContainer">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="color: #000;">Title</th>
                                                    <th style="color: #000;">Date Conducted</th>
                                                    <th style="color: #000;">Venue</th>
                                                    <th style="color: #000;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="employeeTrainingsTable">
                                                <!-- Training rows will be inserted here by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        </div>

                    </div>


            </div>
        </div>
    <?php endif; ?>
  </div>

</div>


<script>
                   

                   const employeeId = <?= $employee['id'] ?>;
                   document.addEventListener('DOMContentLoaded', function() {
                   const searchInput = document.getElementById('searchTraining');
                   const form = searchInput.closest('form');
                   const suggestionContainer = document.getElementById('trainingSuggestions');
                   
                   const style = document.createElement('style');
                   style.textContent = `
                       #trainingSuggestions {
                           display: none;
                           position: absolute;
                           width: calc(75% - 2px);
                           background-color: white;
                           border: 1px solid #ccc;
                           border-radius: 4px;
                           z-index: 1000;
                           max-height: 200px;
                           overflow-y: auto;
                       }
                       
                       .suggestion-item {
                           padding: 8px 12px;
                           cursor: pointer;
                           display: flex;
                           justify-content: space-between;
                           align-items: center;
                       }
                       
                       .suggestion-item:hover {
                           background-color: #e6f7ff;
                           color: #0066cc;
                       }
                       
                       .suggestion-item.no-results {
                           color: #999;
                       }
                       
                       .check-icon {
                           color: #28a745;
                           font-weight: bold;
                       }
                   `;
                   document.head.appendChild(style);
                   
                   // Add event listener to training search input
                   searchInput.addEventListener('input', function() {
                       const term = this.value.trim();
                       
                       if (term.length >= 2) {
                           fetch(`search_trainings.php?term=${encodeURIComponent(term)}`)
                               .then(response => response.json())
                               .then(data => {
                                   suggestionContainer.innerHTML = '';
                                   suggestionContainer.style.display = 'block';
                                   
                                   if (data.length > 0) {
                                       data.forEach(training => {
                                           const suggestion = document.createElement('div');
                                           suggestion.className = 'suggestion-item';
                                           suggestion.dataset.trainingId = training.id;
                                           
                                           const titleSpan = document.createElement('span');
                                           titleSpan.textContent = training.title;
                                           
                                           const checkSpan = document.createElement('span');
                                           checkSpan.className = 'check-icon';
                                           checkSpan.textContent = '';
                                           
                                           suggestion.appendChild(titleSpan);
                                           suggestion.appendChild(checkSpan);
                                           
                                           suggestion.addEventListener('click', () => {
                                               searchInput.value = training.title;
                                               
                                               // Link training to employee
                                               linkTrainingToEmployee(employeeId, training.id, suggestion);
                                               
                                               // Hide suggestions
                                               suggestionContainer.style.display = 'none';
                                           });
                                           suggestionContainer.appendChild(suggestion);
                                       });
                                   } else {
                                       const msg = document.createElement('div');
                                       msg.className = 'suggestion-item no-results';
                                       msg.textContent = 'No trainings found';
                                       suggestionContainer.appendChild(msg);
                                   }
                               })
                               .catch(error => {
                                   console.error('Error fetching training suggestions:', error);
                                   suggestionContainer.innerHTML = '<div class="suggestion-item no-results">No trainings found</div>';
                               });
                       } else {
                           suggestionContainer.style.display = 'none';
                       }
                   });
                   
                   // Close suggestions when clicking outside
                   document.addEventListener('click', function(e) {
                       if (!form.contains(e.target) && !suggestionContainer.contains(e.target)) {
                           suggestionContainer.style.display = 'none';
                       }
                   });
                   
                   // Handle keyboard navigation
                   searchInput.addEventListener('keydown', function(e) {
                       if (e.key === 'Escape') {
                           suggestionContainer.style.display = 'none';
                       }
                   });
                   
                   // Function to link training to employee
                   function linkTrainingToEmployee(employeeId, trainingId, suggestionElement) {
                       fetch('add_training_to_employee.php', {
                           method: 'POST',
                           headers: {
                               'Content-Type': 'application/json',
                           },
                           body: JSON.stringify({
                               employeeId: employeeId,
                               trainingId: trainingId
                           })
                       })
                       .then(response => response.json())
                       .then(data => {
                           if (data.success) {
                               // Show check icon
                               const checkSpan = suggestionElement.querySelector('.check-icon');
                               checkSpan.textContent = '✓';
                               suggestionElement.style.pointerEvents = 'none';
                               suggestionElement.style.opacity = '0.7';
                               
                               // Automatically load updated trainings without page refresh
                               loadEmployeeTrainings(employeeId);
                               
                               // Clear the search input
                               searchInput.value = '';
                               suggestionContainer.style.display = 'none';
                           } else {
                               alert('Error linking training: ' + data.error);
                           }
                       })
                       .catch(error => {
                           console.error('Error linking training:', error);
                           alert('Failed to link training. Please try again.');
                       });
                   }

               });

               // Function to load and display employee trainings
               function loadEmployeeTrainings(employeeId) {
                       fetch(`get_employee_trainings.php?employeeId=${employeeId}`)
                           .then(response => response.json())
                           .then(data => {
                               const trainingsTable = document.getElementById('employeeTrainingsTable');
                               trainingsTable.innerHTML = '';

                               if (data.length > 0) {
                                   data.forEach(training => {
                                       const row = document.createElement('tr');
                                       row.innerHTML = `
                                           <td>${training.title}</td>
                                           <td>${training.date_conducted}</td>
                                           <td>${training.venue}</td>
                                           <td>
                                           <button type="button" class="btn btn-sm btn-danger" onclick="deleteTraining(${employeeId}, ${training.id}, this)">
                                               <i class="fas fa-trash"></i> Remove
                                           </button>

                                           </td>
                                       `;
                                       trainingsTable.appendChild(row);
                                   });
                               } else {
                                   trainingsTable.innerHTML = '<tr><td colspan="4" class="text-center">No trainings linked to this employee.</td></tr>';
                               }
                           })
                           .catch(error => {
                               console.error('Error loading employee trainings:', error);
                           });
                   }

                   function deleteTraining(employeeId, trainingId, buttonElement) {
                       Swal.fire({
                           title: 'Are you sure?',
                           text: "This training will be removed from the employee's record.",
                           icon: 'warning',
                           showCancelButton: true,
                           confirmButtonColor: '#d33',
                           cancelButtonColor: '#3085d6',
                           confirmButtonText: 'Yes, remove it!',
                           reverseButtons: true
                       }).then((result) => {
                           if (result.isConfirmed) {
                               fetch('remove_training_from_employee.php', {
                                   method: 'POST',
                                   headers: {
                                       'Content-Type': 'application/json',
                                   },
                                   body: JSON.stringify({
                                       employeeId: employeeId,
                                       trainingId: trainingId
                                   })
                               })
                               .then(response => response.json())
                               .then(data => {
                                   if (data.success) {
                                       const row = buttonElement.closest('tr');
                                       row.remove();

                                       // Check if there are no rows left
                                       const trainingsTable = document.getElementById('employeeTrainingsTable');
                                       if (trainingsTable.childElementCount === 0) {
                                           trainingsTable.innerHTML = '<tr><td colspan="4" class="text-center">No trainings linked to this employee.</td></tr>';
                                       }

                                       Swal.fire({
                                           icon: 'success',
                                           title: 'Deleted!',
                                           text: 'The training has been successfully removed.',
                                           timer: 2000,
                                           showConfirmButton: false
                                       });

                                   } else {
                                       Swal.fire({
                                           icon: 'error',
                                           title: 'Error',
                                           text: data.error || 'Something went wrong while removing the training.'
                                       });
                                   }
                               })
                               .catch(error => {
                                   console.error('Error removing training:', error);
                                   Swal.fire({
                                       icon: 'error',
                                       title: 'Failed',
                                       text: 'Failed to remove training. Please try again.'
                                   });
                               });
                           }
                       });
                   }

               

                           loadEmployeeTrainings(employeeId);

           

           </script>




<script>
// ===> Modal
var modal = document.getElementById("updateModal");

var btn = document.getElementById("btnModal");

var span = document.getElementsByClassName("close")[0];

btn.onclick = function() {
  modal.style.display = "block";
}

span.onclick = function() {
  modal.style.display = "none";
}

window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

    // ===> Temporary
  function toggleUpdateCard() {
        var updateCard = document.getElementById('updateCard');
        if (updateCard.style.display === 'none' || updateCard.style.display === '') {
            updateCard.style.display = 'block'; 
        } else {
            updateCard.style.display = 'none'; 
        }
    }
</script>

</div>


<script src="./../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    let selectedSubjects = <?= json_encode($employeeSubjects) ?>;
    document.querySelectorAll("#empTeachingSubjectCheckboxes input[type='checkbox']").forEach(function (checkbox) {
        if (selectedSubjects.includes(checkbox.value)) {
            checkbox.checked = true;
        }
    });
});

// Search Teaching Subjects
function searchCheckboxes() {
    let searchValue = document.getElementById("searchTeachingSubjects").value.toLowerCase();
    let checkboxes = document.querySelectorAll("#empTeachingSubjectCheckboxes .form-check");
    
    checkboxes.forEach(item => {
        let label = item.querySelector("label").textContent.toLowerCase();
        if (label.includes(searchValue)) {
            item.style.display = "block"; // ✅ Show matching items
        } else {
            item.style.display = "none"; // ✅ Hide non-matching items
        }
    });
}


// Search Positions
function searchRadioButtonPosition() {
    let searchValuePosition = document.getElementById("searchPositionRadioButton").value.toLowerCase();
    let radioButtons = document.querySelectorAll("#empPositionsRadioButtons .form-check"); // ✅ Select the radio button container

    radioButtons.forEach(item => {
        let labelText = item.querySelector("label").textContent.toLowerCase().trim(); // ✅ Get label text
        if (labelText.includes(searchValuePosition)) {
            item.style.display = "block"; // ✅ Show matching radio buttons
        } else {
            item.style.display = "none"; // ✅ Hide non-matching radio buttons
        }
    });
}


// Search Assigned School
function searchRadioButtonSchool() {
    let searchValueSchool = document.getElementById("searchAssignSchoolRadioButton").value.toLowerCase();
    let radioButtons = document.querySelectorAll("#empAssignSchoolRadioButtons .form-check"); // ✅ Select the radio button container

    radioButtons.forEach(item => {
        let labelText = item.querySelector("label").textContent.toLowerCase().trim(); // ✅ Get label text
        if (labelText.includes(searchValueSchool)) {
            item.style.display = "block"; // ✅ Show matching radio buttons
        } else {
            item.style.display = "none"; // ✅ Hide non-matching radio buttons
        }
    });
}


</script>
</body>
</html>
