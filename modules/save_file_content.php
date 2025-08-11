<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'], $_POST['content'])) {
    $fileId = intval($_POST['file_id']);
    $newContent = $_POST['content'];

    $stmt = $conn->prepare("SELECT filepath, folder_id FROM vault_files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    $stmt->close();

    if ($file && file_exists($file['filepath'])) {
        file_put_contents($file['filepath'], $newContent);
        $_SESSION['success_flash_message'] = "File updated successfully.";
        header("Location: ../dashboard.php?folder_id=" . $file['folder_id']);
        exit;
    } else {
        $_SESSION['error_flash_message'] = "Failed to update file.";
        header("Location: ../dashboard.php");
        exit;
    }


}
