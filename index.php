<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDO Bayawan Teacher Tracker Portal</title>
    <link rel="icon" type="image/x-icon" href="App/assets/images/logo.jpg">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card" style="width: 25rem;">
            <img src="App/assets/images/logo.jpg" class="card-img-top" alt="Portal Logo" height="190">
            <div class="card-body" style="background-color: #20263e;">
                <h5 class="card-title text-center text-white">SDO Bayawan Teacher Tracker Portal</h5>
                <hr>
                <form id="loginForm">
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label text-white">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-white">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="server.js"></script>
</body>
</html>
