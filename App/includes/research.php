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
$researchs = [];


?>

<div class="container mt-2">
    <div class="card shadow border-0">
        <div class="card-body">
            <div class="d-flex align-items-end justify-content-end">
                <button class="btn" style="background-color: #000; color: #fff;" data-toggle="modal" data-target="#addNewResearchModal">
                    <i class="fas fa-plus"></i> New Research
                </button>
            </div>

            <div class="table-responsive">
                
            </div>

        </div>
    </div>
</div>
