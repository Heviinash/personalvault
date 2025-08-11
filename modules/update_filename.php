<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileId = intval($_POST['file_id']);
    $newFilename = trim($_POST['filename']);
    $folderId = intval($_POST['folder_id']);

    if (!empty($newFilename)) {
        // Check if the new filename already exists in the same folder (but not the same file)
        $check = $conn->prepare("SELECT id FROM vault_files WHERE filename = ? AND folder_id = ? AND id != ?");
        $check->bind_param("sii", $newFilename, $folderId, $fileId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $_SESSION['error_flash_message'] = "⚠️ A file with that name already exists in this folder.";
        } else {
            // Safe to update
            $stmt = $conn->prepare("UPDATE vault_files SET filename = ? WHERE id = ?");
            $stmt->bind_param("si", $newFilename, $fileId);
            $stmt->execute();

            $_SESSION['flash_message'] = "✅ Filename updated successfully.";
        }

        $check->close();
    }

    header("Location: ../dashboard.php?folder_id=" . $folderId);
    exit;
}
?>
