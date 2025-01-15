document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggleBtn');
    const passwordInput = document.getElementById('password');

    toggleBtn.addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            toggleBtn.textContent = 'Show';
        }
    });
});

// add client
function toggleAddClientForm() {
    const form = document.getElementById('add-client-form');
    form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
}

// logout
function handleLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "../logInFolder/logIn.html";
    }
}
// handle messages from whatsapp
function directMessage() {
    window.location.href = "https://wa.me/639760998892"
}
    
