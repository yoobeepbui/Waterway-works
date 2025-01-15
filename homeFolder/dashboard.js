document.addEventListener('DOMContentLoaded', () => {
    const settingsButton = document.querySelector('.settings-button');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    settingsButton.addEventListener('click', () => {
        dropdownMenu.style.display = 
            dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.settings')) {
            dropdownMenu.style.display = 'none';
        }
    });
});

//logout
function handleLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "../logInFolder/logIn.html";
    }
}

