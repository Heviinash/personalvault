<?php
session_start();
require '../config/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_id'])) {
    $folderId = intval($_POST['folder_id']);

    // 1. Get all file paths in that folder
    $stmt = $conn->prepare("SELECT filepath FROM vault_files WHERE folder_id = ?");
    $stmt->bind_param("i", $folderId);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Delete files physically
    while ($row = $result->fetch_assoc()) {
        $filePath = $row['filepath'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    $stmt->close();

    // 3. Delete files from DB
    $stmt = $conn->prepare("DELETE FROM vault_files WHERE folder_id = ?");
    $stmt->bind_param("i", $folderId);
    $stmt->execute();
    $stmt->close();

    // 4. Delete the folder itself
    $stmt = $conn->prepare("DELETE FROM vault_folders WHERE id = ?");
    $stmt->bind_param("i", $folderId);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../dashboard.php");
exit;
