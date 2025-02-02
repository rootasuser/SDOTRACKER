<?php

require '../../Config/Config.php';

use App\Config\Config;

session_start();


if (isset($_SESSION['user'])) {

    $username = $_SESSION['user']['username'];

   
    session_unset();
    session_destroy();

    $config = new Config();
    $conn = $config->DB_CONNECTION;

    $stmt = $conn->prepare("INSERT INTO logs (username, action) VALUES (?, ?)");
    $action = $username . " logged out successfully";
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $action);
    $stmt->execute();


    $stmt = null;
    $conn = null;


    header("Location: ../../../index.php");
    exit;
} else {

    header("Location: ../../../index.php");
    exit;
}
