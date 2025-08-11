<?php
session_start();
require '../config/db.php';

$redirectUrl = '../dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    $fileId = intval($_POST['file_id']);
    $folderId = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : null;

    if ($folderId) {
        $redirectUrl = "../dashboard.php?folder_id=$folderId";
    }


    // Get file path
    $stmt = $conn->prepare("SELECT filepath FROM vault_files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $stmt->bind_result($filepath);
    
    if ($stmt->fetch()) {
        $stmt->close();

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $stmt = $conn->prepare("DELETE FROM vault_files WHERE id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $_SESSION['success_flash_message'] = "File deleted successfully.";

    }
}

header("Location: $redirectUrl");
exit;
?>
