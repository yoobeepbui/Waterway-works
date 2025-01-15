<?php
require_once '../phpFolder/client.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "Please fill in both username and password.";
    } else {
        $clientObj = new Client();

        $client = $clientObj->getClientByUsername($username);

        // Debugging, removed it for security purposes
        if ($client) {
            echo "Entered password: $password <br>";
            echo "Stored hash: " . $client['password'] . "<br>";
        }

        if (password_verify($password, $client['password'])) {
            echo "<br>Password matches!";
        } else {
            echo "<br>Password does not match!";
        }
        
        if (password_verify($password, $client['password'])) {
            $_SESSION['client_id'] = $client['id'];
            $_SESSION['username'] = $client['username'];

            echo "Redirecting to: dashboard.php?id=" . $client['id'];
            header("Location: /finalsPhp/homeFolder/dashboard.php?id=" . urlencode($client['client_id']));
            exit();
        } else {
            echo "<br><br>Invalid username or password.";
        }        
    }
} else {
    echo "Access denied. Please use the login form.";
}
