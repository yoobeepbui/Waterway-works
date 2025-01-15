document.addEventListener('DOMContentLoaded', () => {
    const addClientBtn = document.getElementById('add-client-btn');
    const addClientForm = document.getElementById('add-client-form');
    const cancelBtn = document.getElementById('cancel-btn');

    // Show Add Client Form when the link is clicked
    if (addClientBtn) {
        addClientBtn.addEventListener('click', (event) => {
            event.preventDefault();
            toggleAddClientForm();
        });
    }

    // Hide Add Client Form when Cancel button is clicked
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            toggleAddClientForm();
        });
    }

    const addAmountButtons = document.querySelectorAll('.add-amount-btn');
    addAmountButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            // Get the client ID from the button's data attribute
            const clientId = button.getAttribute('data-client-id');
            toggleAddAmountForm(clientId);
        });
    });

    const addWaterButtons = document.querySelectorAll('.add-water-btn');
    addWaterButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const clientId = button.getAttribute('data-client-id');
            toggleAddWaterForm(clientId);
        });
    });
});

// Handle messages from WhatsApp
function directMessage() {
    window.location.href = "https://wa.me/639760998892";
}

// Add client toggle function
function toggleAddClientForm() {
    const form = document.getElementById('add-client-form');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

// Logout function
function handleLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "../logInFolder/logIn.html";
    }
}

// Generic function to toggle forms
function toggleForm(formId) {
    const form = document.getElementById(formId);
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

// Toggle the Add Amount Form visibility
function toggleAddAmountForm(clientId) {
    const form = document.getElementById('add-amount-form-' + clientId);
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

// Toggle form visibility for Add Water Used
function toggleAddWaterForm(clientId) {
    var form = document.getElementById("add-water-form-" + clientId);
    form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
}

