<?php
require_once '../../Config/Config.php';

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    $positionsQuery = "SELECT id, empPosition FROM positions";
    $positionsResult = $conn->query($positionsQuery);

    $schoolsQuery = "SELECT id, empAssignSchool FROM schools";
    $schoolsResult = $conn->query($schoolsQuery);

    $subjectsQuery = "SELECT id, empTeachingSubject FROM subjects";
    $subjectsResult = $conn->query($subjectsQuery);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $empName = ucwords(filter_var(trim($_POST['empName']), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $empNumber = filter_var(trim($_POST['empNumber']), FILTER_SANITIZE_NUMBER_INT);
        $empAddress = ucwords(filter_var(trim($_POST['empAddress']), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $empSex = ucwords(trim($_POST['empSex']));
        $empPosition = intval($_POST['empPosition']);
        $empAssignSchool = intval($_POST['empAssignSchool']);
        $empTeachingSubjects = isset($_POST['empTeachingSubject']) ? array_map('intval', $_POST['empTeachingSubject']) : [];
        $empHistory = ucwords(filter_var(trim($_POST['empHistory']), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $username = htmlspecialchars(trim($_POST['username']));

        $insertQuery = "INSERT INTO employees (empName, empNumber, empAddress, empSex, empPosition_id, empAssignSchool_id, empHistory)
                        VALUES (:empName, :empNumber, :empAddress, :empSex, :empPosition, :empAssignSchool, :empHistory)";

        $stmt = $conn->prepare($insertQuery);
        $stmt->bindParam(':empName', $empName, PDO::PARAM_STR);
        $stmt->bindParam(':empNumber', $empNumber, PDO::PARAM_STR);
        $stmt->bindParam(':empAddress', $empAddress, PDO::PARAM_STR);
        $stmt->bindParam(':empSex', $empSex, PDO::PARAM_STR);
        $stmt->bindParam(':empPosition', $empPosition, PDO::PARAM_INT);
        $stmt->bindParam(':empAssignSchool', $empAssignSchool, PDO::PARAM_INT);
        $stmt->bindParam(':empHistory', $empHistory, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $employeeId = $conn->lastInsertId();

            if (!empty($empTeachingSubjects)) {
                $subjectInsertQuery = "INSERT INTO employee_subjects (employee_id, subject_id) VALUES (:employee_id, :subject_id)";
                $subjectStmt = $conn->prepare($subjectInsertQuery);
                
                foreach ($empTeachingSubjects as $subjectId) {
                    $subjectStmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
                    $subjectStmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
                    $subjectStmt->execute();
                }
            }

            $logQuery = "INSERT INTO logs (username, action, created_at) VALUES (:username, :action, NOW())";
            $logStmt = $conn->prepare($logQuery);
            $logAction = "$username successfully added employee $empName";

            $logStmt->bindParam(':username', $username, PDO::PARAM_STR);
            $logStmt->bindParam(':action', $logAction, PDO::PARAM_STR);
            $logStmt->execute();

            $successMessage = "$empName added successfully.";
        } else {
            $errorMessage = "Failed to add employee.";
        }
    }
} catch (PDOException $e) {
    die("DB Err: " . $e->getMessage());
}
?>


<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <div class="card-header text-center" style="background-color: #20263e; color: #ffffff; font-size: 25px; font-weight: bolder;">Add Employee</div>
        <div class="card-body">
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                
                <!-- Employee Details Form -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="empName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="empName" name="empName" required>
                    </div>

                    <div class="col-md-4">
                        <label for="empNumber" class="form-label">Number</label>
                        <input type="number" class="form-control" id="empNumber" name="empNumber" required>
                    </div>

                    <div class="col-md-4">
                        <label for="empAddress" class="form-label">Address</label>
                        <input type="text" class="form-control" id="empAddress" name="empAddress" required>
                    </div>
                </div>

                <!-- Gender and Position Selection -->
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label for="empSex" class="form-label">Sex</label>
                        <select name="empSex" id="empSex" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="empPosition" class="form-label">Position</label>
                        <select name="empPosition" id="empPosition" class="form-control" required>
                            <?php while ($row = $positionsResult->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $row['id'] . "'>" . $row['empPosition'] . "</option>";
                            } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="empAssignSchool" class="form-label">School Assigned</label>
                        <select name="empAssignSchool" id="empAssignSchool" class="form-control" required>
                            <?php while ($row = $schoolsResult->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $row['id'] . "'>" . $row['empAssignSchool'] . "</option>";
                            } ?>
                        </select>
                    </div>
                </div>

                <!-- Teaching Subjects Selection -->
                <div class="mt-3">
                        <label class="form-label">Teaching Subjects</label>
                        <div class="row" style="max-height: 300px; overflow-y: auto;">
                            <?php 
                            $count = 0;
                            while ($row = $subjectsResult->fetch(PDO::FETCH_ASSOC)) {
                                if ($count % 2 == 0) echo "<div class='col-md-6'>";
                                echo "<div class='form-check'>";
                                echo "<input class='form-check-input' type='checkbox' name='empTeachingSubject[]' value='" . $row['id'] . "' id='subject_" . $row['id'] . "'>";
                                echo "<label class='form-check-label' for='subject_" . $row['id'] . "'>" . $row['empTeachingSubject'] . "</label>";
                                echo "</div>";
                                if ($count % 2 == 1) echo "</div>"; 
                                $count++;
                            }
                            if ($count % 2 == 1) echo "</div>"; 
                            ?>
                        </div>

                        <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empHistory" class="form-label">School History</label>
                            <textarea name="empHistory" id="empHistory" cols="25" rows="5" class="form-control"></textarea>
                        </div>
                    </div>
           


                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

