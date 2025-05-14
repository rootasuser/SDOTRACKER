<?php
require_once '../../Config/Config.php';

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;

    # Display by alphabetical order
    $positionsQuery = "SELECT id, empPosition FROM positions ORDER BY empPosition ASC";
    $positionsResult = $conn->query($positionsQuery);

    $schoolsQuery = "SELECT id, empAssignSchool FROM schools ORDER BY empAssignSchool ASC";
    $schoolsResult = $conn->query($schoolsQuery);

    $subjectsQuery = "SELECT id, empTeachingSubject FROM subjects ORDER BY empTeachingSubject ASC";
    $subjectsResult = $conn->query($subjectsQuery);

    function checkIfEmployeeExists($empName, $conn) {
        $checkQuery = "SELECT COUNT(*) FROM employees WHERE empName = :empName";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bindParam(':empName', $empName, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $empName = strtoupper(trim(htmlspecialchars($_POST['empName'], ENT_NOQUOTES, 'UTF-8')));
        $empNumber = filter_var(trim($_POST['empNumber']), FILTER_SANITIZE_NUMBER_INT);
        $empDob = htmlspecialchars(trim($_POST['empDob']));
        $empCS = htmlspecialchars(trim($_POST['empCS']));
        $empTinNum = htmlspecialchars(trim($_POST['empTinNum']));
        $empDistrict = htmlspecialchars(trim($_POST['empDistrict']));
        $empPlantilla = htmlspecialchars(trim($_POST['empPlantilla']));
        $empAddress = strtoupper(filter_var(trim($_POST['empAddress']), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $empSex = ucwords(trim($_POST['empSex']));
        $empPosition = intval($_POST['empPosition']);
        $empAssignSchool = intval($_POST['empAssignSchool']);
        $empTeachingSubjects = isset($_POST['empTeachingSubject']) ? array_map('intval', $_POST['empTeachingSubject']) : [];
        $empHistory = ucwords(filter_var(trim($_POST['empHistory']), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $username = htmlspecialchars(trim($_POST['username']));
        $empStatus = 'Active';

        # Check if employee name already exists
        if (checkIfEmployeeExists($empName, $conn)) {
            $errorMessage = "$empName already exists.";
        } else {
            $insertQuery = "INSERT INTO employees (empName, empNumber, empDob, empCS, empTinNum, empDistrict, empPlantilla, empAddress, empSex, empPosition_id, empAssignSchool_id, empHistory, empStatus)
                            VALUES (:empName, :empNumber, :empDob, :empCS, :empTinNum, :empDistrict, :empPlantilla, :empAddress, :empSex, :empPosition, :empAssignSchool, :empHistory, :empStatus)";

            $stmt = $conn->prepare($insertQuery);
            $stmt->bindParam(':empName', $empName, PDO::PARAM_STR);
            $stmt->bindParam(':empNumber', $empNumber, PDO::PARAM_STR);
            $stmt->bindParam('empDob', $empDob, PDO::PARAM_STR);
            $stmt->bindParam(':empCS', $empCS, PDO::PARAM_STR);
            $stmt->bindParam(':empTinNum', $empTinNum, PDO::PARAM_STR);
            $stmt->bindParam(':empDistrict', $empDistrict, PDO::PARAM_STR);
            $stmt->bindParam(':empPlantilla', $empPlantilla, PDO::PARAM_STR);
            $stmt->bindParam(':empAddress', $empAddress, PDO::PARAM_STR);
            $stmt->bindParam(':empSex', $empSex, PDO::PARAM_STR);
            $stmt->bindParam(':empPosition', $empPosition, PDO::PARAM_INT);
            $stmt->bindParam(':empAssignSchool', $empAssignSchool, PDO::PARAM_INT);
            $stmt->bindParam(':empHistory', $empHistory, PDO::PARAM_STR);
            $stmt->bindParam(':empStatus', $empStatus, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $employeeId = $conn->lastInsertId();
                if (!empty($empTeachingSubjects)) {
                    $subjectInsertQuery = "INSERT INTO employee_subjects (employee_id, subject_id) VALUES (:employee_id, :subject_id)";
                    $subjectStmt = $conn->prepare($subjectInsertQuery);
                    foreach ($empTeachingSubjects as $subjectId) {
                        $subjectStmt->execute([
                            ':employee_id' => $employeeId,
                            ':subject_id' => $subjectId
                        ]);
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
    }
} catch (PDOException $e) {
    die("DB Err: " . $e->getMessage());
}
?>

<style>
    .form-check-input {
        transform: scale(1.5);
        accent-color: #0FFF50;
    }
</style>

<div class="container mt-4 mb-3">
    <div class="card botder-0 p-3">
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
                <div class="row g-2">
                    <div class="col-md-3">
                        <label for="empName" class="form-label" style="color: #000;">Full Name</label>
                        <input type="text" class="form-control" id="empName" name="empName" required>
                    </div>

                    <div class="col-md-3">
                        <label for="empNumber" class="form-label" style="color: #000;">Employee Number</label>
                        <input type="number" class="form-control" id="empNumber" name="empNumber">
                    </div>

                    <div class="col-md-3">
                        <label for="empDob" class="form-label" style="color: #000;">Date of Birth</label>
                        <input type="date" class="form-control" id="empDob" name="empDob">
                    </div>

                    <div class="col-md-3">
                        <label for="empCS" class="form-label" style="color: #000;">Civil Status</label>
                        <select name="empCS" id="empCS" class="form-control">
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="empTinNum" class="form-label" style="color: #000;">Tin #</label>
                        <input type="text" class="form-control" id="empTinNum" name="empTinNum">
                    </div>

                    <div class="col-md-3">
                        <label for="empPlantilla" class="form-label" style="color: #000;">Plantilla #</label>
                        <input type="text" class="form-control" id="empPlantilla" name="empPlantilla">
                    </div>
                    

                    <div class="col-md-3">
                        <label for="empAddress" class="form-label" style="color: #000;">Address</label>
                        <input type="text" class="form-control" id="empAddress" name="empAddress" required>
                    </div>

                    <div class="col-md-3">
                        <label for="empDistrict" class="form-label" style="color: #000;">District</label>
                        <input type="text" class="form-control" id="empDistrict" name="empDistrict" required>
                    </div>
                    

                    <div class="col-md-3 mt-1">
                                    <label for="empStatus" class="form-label" style="color: #000;">Employee Status</label>
                                    <select name="empStatus" id="empStatus" class="form-control">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="On Leave">On Leave</option>
                                        <option value="Retired">Retired</option>
                                        <option value="Terminated">Terminated</option>
                                    </select>
                                </div>
                </div>

                <!-- Gender and Position Selection -->
                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <label for="empSex" class="form-label" style="color: #000;">Sex</label>
                        <select name="empSex" id="empSex" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                   
            
                    <div class="col-md-3">
                        <label for="empPosition" class="form-label" style="color: #000;">Position</label>
                        <select name="empPosition" id="empPosition" class="form-control" required>
                            <?php while ($row = $positionsResult->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $row['id'] . "'>" . $row['empPosition'] . "</option>";
                            } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="empAssignSchool" class="form-label" style="color: #000;">School Assigned</label>
                        <select name="empAssignSchool" id="empAssignSchool" class="form-control" required>
                            <?php while ($row = $schoolsResult->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $row['id'] . "'>" . $row['empAssignSchool'] . "</option>";
                            } ?>
                        </select>
                    </div>
                </div>
                
                <hr>
                <div class="mb-3 mt-2">
                        <!-- <label for="searchEmployeeSubject" class="form-label" style="color: #000;">Search Teaching Subject/s</label> -->
                        <input type="text" class="form-control" id="searchEmployeeSubject" placeholder="Search teaching subject" onkeyup="searchCheckboxes()">
                    </div>

                    <div class="col-12">
                        <!-- Teaching Subjects Selection -->
                        <div class="mt-3">
                            <label class="form-label" style="color: #000;">Select to add teaching subjects</label>
                            <div class="row" style="max-height: 300px; overflow-y: auto;">
                                <div id="checkboxList"> 
                                    <?php 
                                    $count = 0;
                                    while ($row = $subjectsResult->fetch(PDO::FETCH_ASSOC)) {
                                        if ($count % 2 == 0) echo "<div class='col-md-6'>";
                                        echo "<div class='form-check'>";
                                        echo "<input class='form-check-input' style='font-size: 25px; color: #000;' type='checkbox' name='empTeachingSubject[]' value='" . $row['id'] . "' id='subject_" . $row['id'] . "'>";
                                        echo "<label class='form-check-label text-dark' style='font-size: 16px; color: #000;' for='subject_" . $row['id'] . "'>" . $row['empTeachingSubject'] . "</label>";
                                        echo "<hr>";
                                        echo "</div>";
                                        if ($count % 2 == 1) echo "</div>"; 
                                        $count++;
                                    }
                                    if ($count % 2 == 1) echo "</div>"; 
                                    ?>
                                </div>
                            </div>


                            <!-- Selected Subjects Table -->
                                <div class="row mt-4">
                                    <label class="form-label" style="color: red;">*Selected teaching subjects</label>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <tbody id="selectedSubjectsTableBody">
                                                <tr>
                                                    <td>- No subject selected</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                        
                    </div>


                    </div>



                        <hr>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="empHistory" class="form-label" style="color: #000;">School History</label>
                                <textarea name="empHistory" id="empHistory" cols="25" rows="5" class="form-control w-100"></textarea>
                            </div>
                        </div>

           


                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-dark px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
   function searchCheckboxes() {
    let searchValue = document.getElementById("searchEmployeeSubject").value.toLowerCase();
    let checkboxes = document.querySelectorAll("#checkboxList .form-check");
    
    checkboxes.forEach(item => {
        let label = item.querySelector("label").textContent.toLowerCase();
        if (label.includes(searchValue)) {
            item.style.display = "block"; 
        } else {
            item.style.display = "none";
        }
    });
}



document.addEventListener("DOMContentLoaded", function () {
    function updateSelectedSubjectsTable() {
        let selectedSubjects = [];
        document.querySelectorAll('.form-check-input:checked').forEach((checkbox) => {
            let subjectName = checkbox.nextElementSibling.textContent; 
            selectedSubjects.push(`<tr><td style='color: #000;'><i class="fas fa-check text-danger"></i> ${subjectName}</td></tr>`);
        });

        document.getElementById('selectedSubjectsTableBody').innerHTML = selectedSubjects.length > 0 
            ? selectedSubjects.join("") 
            : "<tr><td style='color: #000;'>- No subject selected</td></tr>";
    }

    document.getElementById('checkboxList').addEventListener("change", function (event) {
        if (event.target.classList.contains("form-check-input")) {
            updateSelectedSubjectsTable();
        }
    });

    updateSelectedSubjectsTable();
});

</script>
