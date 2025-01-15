document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // checks if passwods inputted and stored matches
        if (password !== confirmPassword) {
            event.preventDefault(); // Prevent form submission
            alert("Passwords do not match!");
        }
    });
});
