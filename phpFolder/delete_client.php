<?php
require_once '../phpFolder/client.php';

if (isset($_POST['client_id'])) {
    $clientId = $_POST['client_id'];

    $clientObj = new Client();

    // Perform the deletion
    $deleteSuccess = $clientObj->deleteClient($clientId);

    if ($deleteSuccess) {
        header("Location: ../adminFolder/admin-home.php?message=User deleted successfully");
        exit();
    } else {
        echo "Error deleting client.";
    }
} else {
    echo "No client ID provided!";
}
