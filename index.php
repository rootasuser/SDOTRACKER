<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDO Bayawan Teacher Tracker Portal</title>
    <link rel="icon" type="image/x-icon" href="App/assets/images/favicon1.png">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-image: url('App/assets/images/division-bg_.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .marquee {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.6);
            padding: 5px 0;
        }

        .track {
            display: inline-block;
            white-space: nowrap;
            animation: marquee 20s linear infinite;
        }

        @keyframes marquee {
            from { transform: translateX(100%); }
            to { transform: translateX(-100%); }
        }
    </style>
</head>
<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card border-0" style="width: 25rem; margin-top: 100px;">
            <div class="position-relative">
                <img src="App/assets/images/portal_bg.png" class="card-img-top" alt="Portal Logo" style="width: 100%; height: 190px; object-fit: cover;">
                <div class="marquee">
                    <div class="track text-white">
                        Welcome to SDO Bayawan Teacher Tracker 
                        <span id="autoupdateDate">
                            <?php 
                                date_default_timezone_set('Asia/Manila');
                                echo date('Y');
                            ?>
                        </span> - Login Portal
                    </div>
                </div>
            </div>
            <div class="card-body" style="background-color: #20263e;">
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
