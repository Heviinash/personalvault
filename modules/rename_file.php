<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileId = intval($_POST['file_id']);
    $newFilename = trim($_POST['filename']);

    if (!empty($newFilename)) {
        $stmt = $conn->prepare("UPDATE vault_files SET filename = ? WHERE id = ?");
        $stmt->bind_param("si", $newFilename, $fileId);
        $stmt->execute();
    }
}

header("Location: ../dashboard.php");
exit;
?>
