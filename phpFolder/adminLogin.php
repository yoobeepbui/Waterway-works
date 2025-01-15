<?php
require_once '../phpFolder/client.php';

// Username & password are fixed okkkk
define('ADMIN_USERNAME', '@h2o_admin2024');
define('ADMIN_PASSWORD', 'www2024');

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "Please fill in both username and password.";
    } else {
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;

            header("Location: /finalsPhp/adminFolder/admin-home.php");
            exit();
        } else {
            echo "<br><br>Invalid username or password.";
        }
    }
}
