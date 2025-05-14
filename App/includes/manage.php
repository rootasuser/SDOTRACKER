<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config/Config.php';

try {
    $config = new \App\Config\Config();
    $dbConnection = $config->DB_CONNECTION;
} catch (PDOException $e) {
    error_log('DB Failed: ' . $e->getMessage());
    die('System maintenance in progress. Please try again later.');
}

// Constants
const LOG_ACTIONS = [
    'school' => 'Added new school: %s',
    'position' => 'Added new position: %s',
    'subject' => 'Added new subject: %s'
];

const FORM_CONFIG = [
    'school' => [
        'table' => 'schools',
        'field' => 'empAssignSchool'
    ],
    'position' => [
        'table' => 'positions',
        'field' => 'empPosition'
    ],
    'subject' => [
        'table' => 'subjects',
        'field' => 'empTeachingSubject'
    ]
];


if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$user = $_SESSION['user'];
$username = $user['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        handleFormSubmission($dbConnection, $username);
    } catch (Throwable $e) {
        error_log('System error: ' . $e->getMessage());
        setErrorMessage('An unexpected error occurred. Please try again.');
    }
}

/**
 * Main form handler
 */
function handleFormSubmission(PDO $db, string $username): void {
    foreach (FORM_CONFIG as $type => $config) {
        $field = $config['field'];
        
        if (!isset($_POST[$field])) {
            continue;
        }

        $value = sanitizeInput((string)$_POST[$field]);
        
        if (empty($value)) {
            setErrorMessage("Please provide a valid {$type} name");
            return;
        }

        if (insertRecord($db, $config['table'], $config['field'], $value)) {
            logAction($db, $username, sprintf(LOG_ACTIONS[$type], $value));
            setSuccessMessage("New {$type} added successfully");
        } else {
            setErrorMessage("Failed to add {$type}");
        }
        
        // Only process one form at a time
        return;
    }
}

/**
 * Database operations
 */
function insertRecord(PDO $db, string $table, string $field, string $value): bool {
    $query = "INSERT INTO {$table} ({$field}) VALUES (:value)";
    $stmt = $db->prepare($query);
    return $stmt->execute([':value' => $value]);
}

/**
 * Logging system
 */
function logAction(PDO $db, string $username, string $action): void {
    try {
        $stmt = $db->prepare("
            INSERT INTO logs 
            (username, action, created_at) 
            VALUES (:username, :action, NOW())
        ");
        $stmt->execute([
            ':username' => $username,
            ':action' => $action
        ]);
    } catch (PDOException $e) {
        error_log('Log Fail: ' . $e->getMessage());
    }
}

/**
 * Helpers
 */
function sanitizeInput(string $input): string {
    return strtoupper(trim($input));
}

function setSuccessMessage(string $message): void {
    global $successMessage;
    $successMessage = $message;
}

function setErrorMessage(string $message): void {
    global $errorMessage;
    $errorMessage = $message;
}

?>
<style>
#manageSelectionOption.form-select {
    width: 100%;
    min-width: 250px;
    padding: 12px 16px;
    font-size: 14px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background-color: #ffffff;
    color: #2d3748;
    transition: all 0.3s ease;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
   
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
}

#manageSelectionOption.form-select:hover {
    border-color: #a0aec0;
    cursor: pointer;
}

#manageSelectionOption.form-select:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
    outline: none;
}

#manageSelectionOption.form-select option {
    padding: 12px;
    background: #ffffff;
    color: #2d3748;
}

#manageSelectionOption.form-select option:hover {
    background-color: #f7fafc !important;
}

#manageSelectionOption.form-select option:checked {
    background-color: #ebf4ff !important;
    color: #2d3748;
}

@-moz-document url-prefix() {
    #manageSelectionOption.form-select {
        padding-right: 32px;
        background-position: right 8px center;
    }
}
</style>
<div class="container mt-2">
    <!-- Selection Dropdown -->
    <div class="d-flex justify-content-end mb-3">
        <div class="w-25">
            <!-- <label for="manageSelectionOption" class="form-label">Select Category:</label> -->
            <select class="form-select" id="manageSelectionOption">
                <option value="">-- Select Category --</option>
                <option value="schoolForm">➕ Add Assign School</option>
                <option value="positionForm">➕ Add Position</option>
                <option value="subjectForm">➕ Add Teaching Subject/s</option>
            </select>
        </div>
    </div>

    <!-- Dynamic Forms Container -->
    <div id="formContainer">

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>


    <!-- Dynamic Forms Container -->
    <div id="formContainer">
        <!-- Instruction Message -->
        <div id="instructionMessage" class="card p-3">
            <h5 style="color: #000;"><i class="fas fa-info-circle text-danger"></i> Add Category Guide</h5>
            <p class="mb-0" style="color: #000;">
                1. Select an category from the dropdown above<br>
                2. The corresponding form will appear here<br>
                3. Fill in the required information<br>
                4. Click "Save" to save your entry
            </p>
        </div>



        <!-- School Form -->
        <form id="schoolForm" method="POST" class="dynamic-form card p-3 d-none">
            <h5 style="color: #000;"><i class="fas fa-school" style="color: #000;"></i> Add New Assign School</h5>
            <div class="mb-3">
                <!-- <label class="form-label">Assign School Name</label> -->
                <input type="text" class="form-control" id="empAssignSchool" name="empAssignSchool" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>

        <!-- Position Form -->
        <form id="positionForm" method="POST" class="dynamic-form card p-3 d-none">
            <h5 style="color: #000;"><i class="fas fa-user-tie" style="color: #000;"></i> Add New Position</h5>
            <div class="mb-3">
                <!-- <label class="form-label">Add Position</label> -->
                <input type="text" class="form-control" id="empPosition" name="empPosition" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>

        <!-- Subject Form -->
        <form id="subjectForm" method="POST" class="dynamic-form card p-3 d-none">
            <h5 style="color: #000;"><i class="fas fa-book-open" style="color: #000;"></i> Add New Teaching Subject</h5>
            <div class="mb-3">
                <!-- <label class="form-label">Teaching Subject Name</label> -->
                <input type="text" class="form-control" id="empTeachingSubject" name="empTeachingSubject" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<script>


document.addEventListener('DOMContentLoaded', function() {

    const inputs = document.querySelectorAll('.form-control');

    inputs.forEach(function(input) {
        input.addEventListener('input', function() {
            input.value = input.value.toUpperCase();
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const formSelector = document.getElementById('manageSelectionOption');
    const allForms = document.querySelectorAll('.dynamic-form');
    const instruction = document.getElementById('instructionMessage');

    function toggleForms() {
        const selectedValue = formSelector.value;
        
        // Hide all forms and initially show instruction
        allForms.forEach(form => form.classList.add('d-none'));
        
        if (selectedValue) {
            instruction.classList.add('d-none');
            document.getElementById(selectedValue).classList.remove('d-none');
        } else {
            instruction.classList.remove('d-none');
        }
    }

    // Initial state setup
    toggleForms();
    
    // Event listener for dropdown changes
    formSelector.addEventListener('change', toggleForms);
});
</script>