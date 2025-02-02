<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDO Teacher Tracker Portal</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card" style="width: 25rem;">
            <img src="App/assets/images/logo.jpg" class="card-img-top" alt="Portal Logo" height="190">
            <div class="card-body" style="background-color: #20263e;">
                <h5 class="card-title text-center text-white">SDO Teacher Tracker Portal</h5>
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
    <script>
 document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    // Get form data
    const formData = new FormData(this);

    // Show loading or prevent further clicks
    const submitButton = this.querySelector('button');
    submitButton.disabled = true;
    submitButton.textContent = 'Logging in...';

    // Perform the fetch request to login.php
    fetch('App/Modules/Auth/login.php?action=login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())  // Get the response as text first
    .then(text => {
        try {
            const data = JSON.parse(text);  // Try to parse the text as JSON
            console.log(data);  // Log the parsed data

            if (data.success) {
                // Redirect on success
                window.location.href = 'App/Modules/User/dashboard';
            } else {
                // Show error message if login fails
                const errorMessage = document.getElementById('errorMessage');
                errorMessage.textContent = data.message;
                errorMessage.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Failed to parse JSON:', error);
            console.log('Response was:', text);  // Log the raw response to help debug
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = 'Error Processing response.';
            errorMessage.classList.remove('d-none');
        }
        submitButton.disabled = false;
        submitButton.textContent = 'Login';
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMessage = document.getElementById('errorMessage');
        errorMessage.textContent = 'An unexpected error occurred. Please try again.';
        errorMessage.classList.remove('d-none');
        submitButton.disabled = false;
        submitButton.textContent = 'Login';
    });
});

    </script>
</body>
</html>
