<?php
require_once '../../Config/Config.php';

class Logs {
    private $conn;

    public function __construct() {
        $config = new \App\Config\Config();
        $this->conn = $config->DB_CONNECTION;

        if (!$this->conn) {
            throw new Exception("Db Fail.");
        }
    }

    public function getAllLogs() {
        try {
            $query = "SELECT * FROM logs ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Err in getAllLogs: " . $e->getMessage());
            return false; 
        }
    }

    public function deleteLogs($ids) {
        if (empty($ids)) {
            return true; 
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $query = "DELETE FROM logs WHERE id IN (" . $placeholders . ")";
            $stmt = $this->conn->prepare($query);

            foreach ($ids as $key => $id) {
                $stmt->bindValue($key + 1, intval($id), PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Err in deleteLogs: " . $e->getMessage());
            return false; 
        }
    }
}

$logManager = new Logs();
$successMessage = null;
$errorMessage = null;
$logs = []; 

if (isset($_POST['delete_logs'])) {
    if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
        $idsToDelete = $_POST['selected_ids'];

        if ($logManager->deleteLogs($idsToDelete)) {
            $successMessage = "Logs deleted successfully!";
        } else {
            $errorMessage = "Failed to delete logs. Please check the error log.";
        }
    } else {
        $errorMessage = "No logs selected for deletion.";
    }
}

$logsResult = $logManager->getAllLogs();

if ($logsResult !== false) {
    $logs = $logsResult;
} else {
    $errorMessage = "Failed to retrieve logs.";
}
?>

<script src="path/to/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="/SDOTRACKER/css/jquery.dataTables.min.css">
<script type="text/javascript" charset="utf8" src="/SDOTRACKER/css/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="/SDOTRACKER/css/all.min.css">
<link rel="stylesheet" href="/SDOTRACKER/node_modules/@fortawesome/fontawesome-free/css/all.min.css" />
<link rel="stylesheet" href="/SDOTRACKER/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .table-container {
        overflow-x: auto;
        cursor: pointer;
    }
</style>
<div class="container mt-2">
    <div class="card">
        <div class="card-body">
            <?php
            if (isset($successMessage)) {
                echo "<div class='alert alert-success' role='alert'>$successMessage</div>";
            } elseif (isset($errorMessage)) {
                echo "<div class='alert alert-danger' role='alert'>$errorMessage</div>";
            }
            ?>

            <form method="POST" action="">
                <div class="float-end mb-2">
                    <button type="submit" class="btn btn-danger" name="delete_logs">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
                
                <div class="table-container table-responsive">
                    <?php if (!empty($logs)): ?>
                        <table id="logsTable" class="table table-bordered text-dark" style="max-height: 400px;">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Action</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><input type="checkbox" name="selected_ids[]" value="<?= htmlspecialchars($log['id']) ?>"></td>
                                        <td><?= htmlspecialchars($log['id']) ?></td>
                                        <td><?= htmlspecialchars($log['username']) ?></td>
                                        <td><?= htmlspecialchars($log['action']) ?></td>
                                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No logs found.</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#logsTable').DataTable();
        
        $('#selectAll').on('click', function() {
            var isChecked = this.checked;
            $('input[type="checkbox"]').prop('checked', isChecked); 
        });
    });
</script>