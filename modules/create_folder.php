<?php

require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $folder = trim($_POST['folder_name']);
  if (!empty($folder)) {
    $stmt = $conn->prepare("INSERT INTO vault_folders (name) VALUES (?)");
    $stmt->bind_param("s", $folder);
    $stmt->execute();
  }
}
header("Location: ../dashboard.php");
exit;

