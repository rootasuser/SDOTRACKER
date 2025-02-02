<?php
require_once '../../Config/Config.php';

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    // Fetch static data
    $positionsQuery = "SELECT id, empPosition FROM positions";
    $positionsResult = $conn->query($positionsQuery);

    $schoolsQuery = "SELECT id, empAssignSchool FROM schools";
    $schoolsResult = $conn->query($schoolsQuery);

    $subjectsQuery = "SELECT id, empTeachingSubject FROM subjects";
    $subjectsResult = $conn->query($subjectsQuery);
    
    // Initialize variables
    $employee = null;
    $error = '';
    $successMessage = ''; 
    $errorMessage = ''; 

    // Handle search
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
        $searchTerm = trim($_POST['search']);
        
        try {
            // Fetch employee data with joins.
            // We now select both subject ids and subject names for display.
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

    // Handle update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empId'])) {
        try {
            // Prepare update query for the employees table.
            $updateStmt = $conn->prepare("
                UPDATE employees 
                SET empName = :empName, 
                    empNumber = :empNumber, 
                    empAddress = :empAddress, 
                    empSex = :empSex, 
                    empPosition_id = :empPosition, 
                    empAssignSchool_id = :empAssignSchool, 
                    empHistory = :empHistory
                WHERE id = :empId
            ");
            
            // Get form values.
            // empTeachingSubject should be an array of subject ids.
            $empTeachingSubject = isset($_POST['empTeachingSubject']) ? $_POST['empTeachingSubject'] : [];

            // Execute the update for the employees table.
            $updateStmt->execute([
                ':empName'         => $_POST['empName'],
                ':empNumber'       => $_POST['empNumber'],
                ':empAddress'      => $_POST['empAddress'],
                ':empSex'          => $_POST['empSex'],
                ':empPosition'     => $_POST['empPosition'],      // Should be a valid positions.id
                ':empAssignSchool' => $_POST['empAssignSchool'],  // Should be a valid schools.id
                ':empHistory'      => $_POST['empHistory'],
                ':empId'           => $_POST['empId']
            ]);

            // Update the employee_subjects table.
            // First, delete old subject assignments for the employee.
            $deleteSubjectsStmt = $conn->prepare("DELETE FROM employee_subjects WHERE employee_id = :empId");
            $deleteSubjectsStmt->execute([':empId' => $_POST['empId']]);

            // Then, insert the new subject assignments.
            foreach ($empTeachingSubject as $subjectId) {
                // Cast to integer to be sure.
                $subjectId = (int)$subjectId;
                
                // Optionally verify that the subject exists before inserting.
                $insertSubjectStmt = $conn->prepare("INSERT INTO employee_subjects (employee_id, subject_id) VALUES (:empId, :subjectId)");
                $insertSubjectStmt->execute([
                    ':empId'    => $_POST['empId'],
                    ':subjectId'=> $subjectId
                ]);
            }

            // Sanitize username and prepare log entry.
            $username = htmlspecialchars(trim($_SESSION['user']['username']));

            // Insert log entry after successful update.
            $logStmt = $conn->prepare("INSERT INTO logs (username, action, created_at) VALUES (:username, :action, NOW())");
            $logStmt->execute([
                ':username' => $username, 
                ':action'   => $username . " successfully updated the details of " . $_POST['empName']
            ]);

            // Set the success message.
            $successMessage = "Employee details successfully updated: " . htmlspecialchars($_POST['empName']);
        } catch (PDOException $e) {
            // Set error message in case of failure.
            $errorMessage = "Update failed: " . $e->getMessage();
        }
    }

    // For pre-checking subject checkboxes in the form,
    // if an employee is found then use the subjectIds (if set)
    $employeeSubjects = ($employee && !empty($employee['subjectIds']))
                        ? explode(',', $employee['subjectIds'])
                        : [];
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Employee</title>
  <!-- You can include Bootstrap CSS here -->
</head>
<body>
<div class="container">
    <div class="mb-3">
        <h1>Search Employee <i class="fas fa-arrow-alt-circle-right"></i> <?= htmlspecialchars($employee['empName'] ?? ''); ?></h1>
        <form action="" method="POST">
            <input type="text" class="form-control" name="search" 
                   value="<?= htmlspecialchars($_POST['search'] ?? '') ?>" 
                   placeholder="Search by name or number" required>
            <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-search"></i> Search</button>
            <!-- Display error message if search fails -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger mt-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </form>

        <!-- Display success message if employee details updated -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show mt-2">
                <?= $successMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Display error message if error occurs while updating employee -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-2">
                <?= $errorMessage; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($employee): ?>
        <div class="card mt-4">
            <div class="card-header text-center" style="background-color: #20263e; color: #ffffff; font-size: 20px; font-weight: bolder;">
                Update Employee Information
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="empId" value="<?= htmlspecialchars($employee['id']) ?>">
                    <input type="hidden" name="username" value="<?= htmlspecialchars($username ?? '') ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="empName" 
                                   value="<?= htmlspecialchars($employee['empName']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Number</label>
                            <input type="number" class="form-control" name="empNumber" 
                                   value="<?= htmlspecialchars($employee['empNumber']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="empAddress" 
                                   value="<?= htmlspecialchars($employee['empAddress']) ?>" required>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item"><strong>Current Sex:</strong> <?= htmlspecialchars($employee['empSex'] ?? '') ?></li>
                                <li class="list-group-item"><strong>Current Position:</strong> <?= htmlspecialchars($employee['empPosition'] ?? '') ?></li>
                                <li class="list-group-item"><strong>Current School Assigned:</strong> <?= htmlspecialchars($employee['empAssignSchool'] ?? '') ?></li>
                                <li class="list-group-item">
                                    <strong>Current Teaching Subject/s:</strong>
                                    <?= htmlspecialchars($employee['subjectNames'] ?? '') ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Update Sex -->
                        <div class="col-md-4">
                            <label class="form-label">Select Update Sex</label>
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
                            <label class="form-label">Select Update Position</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($positionsResult as $position): ?>
                                    <label class="d-block">
                                        <input type="radio" name="empPosition" value="<?= htmlspecialchars($position['id']) ?>"
                                            <?= ($position['id'] == $employee['empPosition_id']) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($position['empPosition']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Update School -->
                        <div class="col-md-4">
                            <label class="form-label">Select Update School Assigned</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($schoolsResult as $school): ?>
                                    <label class="d-block">
                                        <input type="radio" name="empAssignSchool" value="<?= htmlspecialchars($school['id']) ?>"
                                            <?= ($school['id'] == $employee['empAssignSchool_id']) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($school['empAssignSchool']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Update Subjects and History -->
                    <div class="row mt-3">
                        <!-- Update Subjects -->
                        <div class="col-md-4" style="max-height: 200px; overflow-y: auto;">
                            <label class="form-label">Teaching Subject/s</label>
                            <div id="empTeachingSubjectCheckboxes">
                                <?php foreach ($subjectsResult as $subject): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="empTeachingSubject[]" 
                                               value="<?= htmlspecialchars($subject['id']) ?>"
                                               <?= in_array($subject['id'], $employeeSubjects) ? 'checked' : '' ?>>
                                        <label class="form-check-label">
                                            <?= htmlspecialchars($subject['empTeachingSubject']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Update History -->
                        <div class="col-md-8">
                            <label class="form-label">School History</label>
                            <textarea class="form-control" name="empHistory" rows="5" required><?= htmlspecialchars($employee['empHistory']) ?></textarea>
                        </div>
                    </div>
                    <div class="mt-2 d-flex justify-content-end align-items-end">
                    <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                   
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let selectedSubjects = <?= json_encode($employeeSubjects) ?>;
    document.querySelectorAll("#empTeachingSubjectCheckboxes input[type='checkbox']").forEach(function (checkbox) {
        if (selectedSubjects.includes(checkbox.value)) {
            checkbox.checked = true;
        }
    });
});
</script>
</body>
</html>
