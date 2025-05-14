<?php

use App\Config\Config;

session_start();

if (session_id() == '' || !isset($_SESSION['user'])) 
{
    session_regenerate_id(true);
}

if (!isset($_SESSION['user'])) 
{
    header("Location: ../../../index.php");
    exit;
}

$timeoutDuration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutDuration)) {
    $username = $_SESSION['user']['username']; 
    session_unset();
    session_destroy();

    require '../../Config/Config.php';  

    $config = new Config(); 
    $conn = $config->DB_CONNECTION; 

    $stmt = $conn->prepare("INSERT INTO logs (username, action) VALUES (?, ?)");
    $action = $username . " automatically logged out due to inactivity";
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $action);
    $stmt->execute();
    
    $stmt = null; 
    $conn = null; 

    header("Location: ../../../index.php");
    exit;
}

$_SESSION['last_activity'] = time();
$user = $_SESSION['user'];


?>
