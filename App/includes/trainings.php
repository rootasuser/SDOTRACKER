<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$configFilePath = __DIR__ . '/../Config/Config.php';
if (!file_exists($configFilePath)) {
    die('Err: Config file missing at ' . $configFilePath);
}
require_once($configFilePath);

try {
    $config = new \App\Config\Config();
    $conn = $config->DB_CONNECTION;
} catch (PDOException $e) {
    die("DB conn failed: " . $e->getMessage());
}

if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

$user = $_SESSION['user'];
$successMessage = '';
$sweetalert = '';
$trainings = [];

// DB operations for trainings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trainingTitle'], $_POST['trainingDate'], $_POST['trainingVenue'])) {
    try {
        $trainingTitle = htmlspecialchars($_POST['trainingTitle']);
        $trainingDate = htmlspecialchars($_POST['trainingDate']);
        $trainingVenue = htmlspecialchars($_POST['trainingVenue']);
        
        $stmt = $conn->prepare("INSERT INTO trainings (title, date_conducted, venue) VALUES (:title, :date, :venue)");
        $stmt->execute([
            ':title' => $trainingTitle,
            ':date' => $trainingDate,
            ':venue' => $trainingVenue
        ]);
        
        $successMessage = 'Training added successfully!';
        $sweetalert = 'show';
    } catch (PDOException $e) {
        die("Error adding training: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editTrainingId'], $_POST['editTrainingTitle'], $_POST['editTrainingDate'], $_POST['editTrainingVenue'])) {
    try {
        $trainingId = htmlspecialchars($_POST['editTrainingId']);
        $trainingTitle = htmlspecialchars($_POST['editTrainingTitle']);
        $trainingDate = htmlspecialchars($_POST['editTrainingDate']);
        $trainingVenue = htmlspecialchars($_POST['editTrainingVenue']);
        
        $stmt = $conn->prepare("UPDATE trainings SET title = :title, date_conducted = :date, venue = :venue WHERE id = :id");
        $stmt->execute([
            ':id' => $trainingId,
            ':title' => $trainingTitle,
            ':date' => $trainingDate,
            ':venue' => $trainingVenue
        ]);
        
        $successMessage = 'Training updated successfully!';
        $sweetalert = 'show';
    } catch (PDOException $e) {
        die("Error updating training: " . $e->getMessage());
    }
}

/**
 * IN THIS PART:: DELETE FIRST THE DATA CONNECTED TO (trainings) tbl in employee_trainings tbl
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteTrainingId'])) {
    try {
        $trainingId = htmlspecialchars($_POST['deleteTrainingId']);
        
        // Delete associated employee trainings
        $stmt = $conn->prepare("DELETE FROM employee_trainings WHERE training_id = :trainingId");
        $stmt->execute([':trainingId' => $trainingId]);
        
        // Delete the training
        $stmt = $conn->prepare("DELETE FROM trainings WHERE id = :id");
        $stmt->execute([':id' => $trainingId]);
        
        $successMessage = 'Training and associated employee trainings deleted successfully!';
        $sweetalert = 'show';
    } catch (Exception $e) {
        die("Err deleting training: " . $e->getMessage());
    }
}

// Get all trainings
try {
    $stmt = $conn->prepare("SELECT * FROM trainings ORDER BY date_conducted DESC");
    $stmt->execute();
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error loading training records.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Records</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        th, td {
            text-wrap: nowrap;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card border-0">
            <div class="card-header" style="color: #000;">Training Attended</div>
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between mb-3">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search..." onkeyup="searchTraining()">
                    </div>
                    <button class="btn btn-dark" style="background-color: #000; color: #fff;" data-bs-toggle="modal" data-bs-target="#addNewTrainingModal">
                        <i class="fas fa-plus"></i> New Training
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="trainingTable">
                        <thead>
                            <tr>
                                <th style="color: #000;">Title of Training</th>
                                <th style="color: #000;">Date Conducted</th>
                                <th style="color: #000;">Training Venue</th>
                                <th style="color: #000;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="trainingTableBody">
                            <?php if (!empty($trainings)): ?>
                                <?php foreach ($trainings as $training): ?>
                                    <tr data-id="<?php echo $training['id']; ?>">
                                        <td>
                                            <a href="#" 
                                            class="text-decoration-none" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#trainingEmployeesModal<?php echo $training['id']; ?>">
                                                <?php echo htmlspecialchars($training['title']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($training['date_conducted']); ?></td>
                                        <td><?php echo htmlspecialchars($training['venue']); ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewTrainingModal<?php echo $training['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTrainingModal<?php echo $training['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $training['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                 <!-- Training Employees Modal -->
                                    <div class="modal fade" id="trainingEmployeesModal<?php echo $training['id']; ?>" tabindex="-1" aria-labelledby="trainingEmployeesModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="trainingEmployeesModalLabel" style="color: #000;">Employees Related to Training <i class="fas fa-arrow-right"></i> <span id="training-title-show" style="color: blue;"><?php echo htmlspecialchars($training['title']); ?></span></h5>
                                                </div>
                                                <div class="d-flex mt-1 mb-1 mx-2 ms-2">
                                                    <input type="text" class="form-control" id="searchingInput<?php echo $training['id']; ?>" placeholder="Search..." onkeyup="searchingTrainingModal(<?php echo $training['id']; ?>)">
                                                </div>
                                                <div class="modal-body" id="trainingEmployeesModalBody<?php echo $training['id']; ?>">
                                                    <!-- Employees list will be loaded here -->
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No training records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Training Modal -->
    <div class="modal fade" id="addNewTrainingModal" tabindex="-1" aria-labelledby="addNewTrainingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addTrainingForm" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addNewTrainingModalLabel" style="color: #000;">Add New Training</h5>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="trainingTitle" class="form-label" style="color: #000;">Title of Training</label>
                            <input type="text" class="form-control" id="trainingTitle" name="trainingTitle" required>
                        </div>

                        <div class="mb-3">
                            <label for="trainingDate" class="form-label" style="color: #000;">Date Conducted</label>
                            <input type="date" class="form-control" id="trainingDate" name="trainingDate" required>
                        </div>

                        <div class="mb-3">
                            <label for="trainingVenue" class="form-label" style="color: #000;">Training Venue</label>
                            <input type="text" class="form-control" id="trainingVenue" name="trainingVenue" required>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Training</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Training Modal -->
    <?php if (!empty($trainings)): ?>
        <?php foreach ($trainings as $training): ?>
        <div class="modal fade" id="viewTrainingModal<?php echo $training['id']; ?>" tabindex="-1" aria-labelledby="viewTrainingModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewTrainingModalLabel" style="color: #000;">Training Details</h5>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="viewTrainingTitle" class="form-label" style="color: #000;">Title of Training</label>
                            <p><?php echo htmlspecialchars($training['title']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label for="viewTrainingDate" class="form-label" style="color: #000;">Date Conducted</label>
                            <p><?php echo htmlspecialchars($training['date_conducted']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label for="viewTrainingVenue" class="form-label" style="color: #000;">Training Venue</label>
                            <p><?php echo htmlspecialchars($training['venue']); ?></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Edit Training Modal -->
    <?php if (!empty($trainings)): ?>
        <?php foreach ($trainings as $training): ?>
        <div class="modal fade" id="editTrainingModal<?php echo $training['id']; ?>" tabindex="-1" aria-labelledby="editTrainingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="editTrainingForm" method="post">
                        <input type="hidden" name="editTrainingId" value="<?php echo $training['id']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTrainingModalLabel" style="color: #000;">Edit Training</h5>
                        </div>
                        
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="editTrainingTitle" class="form-label" style="color: #000;">Title of Training</label>
                                <input type="text" class="form-control" id="editTrainingTitle" name="editTrainingTitle" value="<?php echo htmlspecialchars($training['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="editTrainingDate" class="form-label" style="color: #000;">Date Conducted</label>
                                <input type="date" class="form-control" id="editTrainingDate" name="editTrainingDate" value="<?php echo htmlspecialchars($training['date_conducted']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="editTrainingVenue" class="form-label" style="color: #000;">Training Venue</label>
                                <input type="text" class="form-control" id="editTrainingVenue" name="editTrainingVenue" value="<?php echo htmlspecialchars($training['venue']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Training</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.all.min.js"></script>
    <script>
      

        function searchTraining() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("trainingTableBody");
            var rows = table.getElementsByTagName("tr");

            for (var i = 0; i < rows.length; i++) {
                var cols = rows[i].getElementsByTagName("td");
                var matchFound = false;

                for (var j = 0; j < cols.length - 1; j++) { 
                    var txtValue = cols[j].textContent || cols[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        matchFound = true;
                        break;
                    }
                }

                rows[i].style.display = matchFound ? "" : "none";
            }
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'deleteTrainingId';
                    input.value = id;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Show success message after adding, editing, or deleting training
        if ('<?php echo $sweetalert; ?>' === 'show') {
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: '<?php echo $successMessage; ?>',
                showConfirmButton: false,
                timer: 3000
            });
        }

// Search table data inside the modal
function searchingTrainingModal(modalId) {
    // Get the search input with the specific ID
    var input = document.getElementById(`searchingInput${modalId}`);
    if (!input) return;

    var filter = input.value.toUpperCase();

    // Get the modal body with the specific ID
    var modalBody = document.getElementById(`trainingEmployeesModalBody${modalId}`);
    if (!modalBody) return;

    // Get the table within the modal body
    var table = modalBody.querySelector('table');
    if (!table) return;

    // Get all rows in the table
    var tr = table.getElementsByTagName("tr");

    // Loop through all rows and hide those that don't match the search
    for (var i = 0; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            var txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

// Initialize search functionality when modal is shown
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.relatedTarget.closest('.modal');
        if (!modal) return;

        const modalId = modal.getAttribute('id');
        if (modalId && modalId.startsWith('trainingEmployeesModal')) {
            const trainingId = modalId.replace('trainingEmployeesModal', '');
            loadTrainingEmployees(trainingId, modal);

            // Setup search functionality for this modal
            const searchInput = document.getElementById(`searchingInput${trainingId}`);
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    searchingTrainingModal(trainingId);
                });
            }
        }
    });
});


        document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const modalId = this.getAttribute('id');
            if (modalId.startsWith('trainingEmployeesModal')) {
                const trainingId = modalId.replace('trainingEmployeesModal', '');
                loadTrainingEmployees(trainingId);
            }
        });
    });
});

function loadTrainingEmployees(trainingId) {
    const modalBody = document.getElementById(`trainingEmployeesModalBody${trainingId}`);


    // Show loading indicator
    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // GET employees from the server
    fetch(`get_training_employees.php?trainingId=${trainingId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Update modal body with employee data
            if (data.length > 0) {
                let html = '<table class="table table-striped">';
                html += '<thead><tr><th>Name</th><th>Position</th><th>School Assigned</th></tr></thead><tbody id="trainingEmployeesModalBody">';
                data.forEach(employee => {

                    html += `<tr>
                        <td>${employee.empName}</td>
                        <td>${employee.empPosition}</td>
                        <td>${employee.empAssignSchool}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                modalBody.innerHTML = html;
            } else if (data.error) {
                modalBody.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                modalBody.innerHTML = '<div class="alert alert-info">No employees found for this training.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading employees:', error);
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading employees: ${error.message}</div>`;
        });
}
    </script>
</body>
</html>