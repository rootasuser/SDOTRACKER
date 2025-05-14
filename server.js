document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    const submitButton = this.querySelector('button');
    submitButton.disabled = true;
    submitButton.textContent = 'Logging in...';

    fetch('App/Modules/Auth/login.php?action=login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())  
    .then(text => {
        try {
            const data = JSON.parse(text);  

            if (data.success) {

                window.location.href = 'App/Modules/User/dashboard';
            } else {
     
                const errorMessage = document.getElementById('errorMessage');
                errorMessage.textContent = data.message;
                errorMessage.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Failed to parse JSON:', error);
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
