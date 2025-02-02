<?php
require_once '../../Config/Config.php';

class Logs {
    private $conn;

    public function __construct() {
        $config = new \App\Config\Config();
        $this->conn = $config->DB_CONNECTION;
    }

    public function getAllLogs() {
        $query = "SELECT * FROM logs ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteLogs($ids) {
        $query = "DELETE FROM logs WHERE id IN (" . implode(',', array_map('intval', $ids)) . ")";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}


$logManager = new Logs();

if (isset($_POST['delete_logs'])) {
    if (isset($_POST['selected_ids'])) {
        $logManager->deleteLogs($_POST['selected_ids']);
        $successMessage = "Logs deleted successfully!";
    } else {
        $errorMessage = "No logs selected for deletion.";
    }
}

$logs = $logManager->getAllLogs();
?>

<div class="container mt-2">
    <div class="card">
        <div class="card-header text-center" style="background-color: #20263e; color: #ffffff; font-size: 25px; font-weight: bolder;">History Logs</div>
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
                    <button type="submit" class="btn btn-dark" name="delete_logs">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table id="logsTable" class="table table-bordered">
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
                                    <td><input type="checkbox" name="selected_ids[]" value="<?= $log['id'] ?>"></td>
                                    <td><?= $log['id'] ?></td>
                                    <td><?= htmlspecialchars($log['username']) ?></td>
                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                    <td><?= $log['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
            $('input[type="checkbox"]').each(function() {
                this.checked = isChecked;
            });
        });
    });
</script>