<?php
require_once '../../Config/Config.php';

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;

$positionsQuery = "SELECT empPosition FROM positions";
$positionsResult = $conn->query($positionsQuery);

$schoolsQuery = "SELECT empAssignSchool FROM schools";
$schoolsResult = $conn->query($schoolsQuery);

$subjectsQuery = "SELECT empTeachingSubject FROM subjects";
$subjectsResult = $conn->query($subjectsQuery);

if (!$positionsResult || !$schoolsResult || !$subjectsResult) {
    die('Err fetch data.');
}

$successMessage = '';
$errorMessage = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $empName = strtoupper(htmlspecialchars(trim($_POST['empName']))); 
    $empNumber = htmlspecialchars(trim($_POST['empNumber']));
    $empAddress = strtoupper(htmlspecialchars(trim($_POST['empAddress']))); 
    $empSex = strtoupper(htmlspecialchars(trim($_POST['empSex']))); 
    $empPosition = strtoupper(htmlspecialchars(trim($_POST['empPosition']))); 
    $empAssignSchool = strtoupper(htmlspecialchars(trim($_POST['empAssignSchool']))); 
    $empTeachingSubject = isset($_POST['empTeachingSubject']) ? implode(",", $_POST['empTeachingSubject']) : '';
    $empHistory = strtoupper(htmlspecialchars(trim($_POST['empHistory'])));

  
    if (empty($empName) || empty($empNumber) || empty($empAddress)) {
        $errorMessage = "Fill all fields is required.";
    } else {
   
        $insertQuery = "INSERT INTO employees (empName, empNumber, empAddress, empSex, empPosition, empAssignSchool, empTeachingSubject, empHistory)
                        VALUES (:empName, :empNumber, :empAddress, :empSex, :empPosition, :empAssignSchool, :empTeachingSubject, :empHistory)";
        
        $stmt = $conn->prepare($insertQuery);

        $stmt->bindParam(':empName', $empName);
        $stmt->bindParam(':empNumber', $empNumber);
        $stmt->bindParam(':empAddress', $empAddress);
        $stmt->bindParam(':empSex', $empSex);
        $stmt->bindParam(':empPosition', $empPosition);
        $stmt->bindParam(':empAssignSchool', $empAssignSchool);
        $stmt->bindParam(':empTeachingSubject', $empTeachingSubject);
        $stmt->bindParam(':empHistory', $empHistory);

        if ($stmt->execute()) {
            $username = htmlspecialchars(trim($_POST['username']));

            $logQuery = "INSERT INTO logs (username, action, created_at) 
                         VALUES (:username, :action, NOW())";
            $logStmt = $conn->prepare($logQuery);
            $logAction = "$username successfully added employee $empName";

            $logStmt->bindParam(':username', $username);
            $logStmt->bindParam(':action', $logAction);

            if ($logStmt->execute()) {
     
                $successMessage = "You have successfully added the employee.";
            } else {
                $errorMessage = "Failed to insert log entry.";
            }
        } else {
            $errorMessage = "Failed to add the employee.";
        }
    }
}
?>

<!-- HTML Form -->
<div class="container">
    <div class="card">
        <div class="card-header" style="background-color: #20263e; color: #ffffff; font-size: 20px; font-weight: bolder;">
            Add Employee Information
        </div>
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
            
            <form action="" method="POST">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">

                <!-- Employee Details Form -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="empName" name="empName" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empNumber" class="form-label">Number</label>
                            <input type="number" class="form-control" id="empNumber" name="empNumber" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empAddress" class="form-label">Address</label>
                            <input type="text" class="form-control" id="empAddress" name="empAddress" required>
                        </div>
                    </div>
                </div>

                <!-- Gender and Position Selection -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empSex" class="form-label">Sex</label>
                            <select name="empSex" id="empSex" class="form-control">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empPosition" class="form-label">Position</label>
                            <select name="empPosition" id="empPosition" class="form-control">
                                <?php
                                if ($positionsResult->rowCount() > 0) {
                                    while ($row = $positionsResult->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $row['empPosition'] . "'>" . $row['empPosition'] . "</option>";
                                    }
                                } else {
                                    echo "<option>No positions available</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empAssignSchool" class="form-label">School Assigned</label>
                            <select name="empAssignSchool" id="empAssignSchool" class="form-control">
                                <?php
                                if ($schoolsResult->rowCount() > 0) {
                                    while ($row = $schoolsResult->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $row['empAssignSchool'] . "'>" . $row['empAssignSchool'] . "</option>";
                                    }
                                } else {
                                    echo "<option>No schools available</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Subject Checkboxes -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Subject/s Teaching</label>
                            <div id="empTeachingSubjectCheckboxes" style="max-height: 200px; overflow-y: auto;">
                                <?php
                                if ($subjectsResult->rowCount() > 0) {
                                    while ($row = $subjectsResult->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<div class='form-check'>";
                                        echo "<input class='form-check-input' type='checkbox' name='empTeachingSubject[]' value='" . $row['empTeachingSubject'] . "' id='subject_" . $row['empTeachingSubject'] . "'>";
                                        echo "<label class='form-check-label' for='subject_" . $row['empTeachingSubject'] . "'>" . $row['empTeachingSubject'] . "</label>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p>No subjects available</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="empHistory" class="form-label">School History</label>
                            <textarea name="empHistory" id="empHistory" cols="25" rows="5" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    <?php if ($successMessage): ?>
        setTimeout(function() {
            var successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 5000);
    <?php endif; ?>
</script>
