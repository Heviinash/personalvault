<?php
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['uploaded_files'])) {
    $folderId = !empty($_POST['folder_id']) ? intval($_POST['folder_id']) : null;
    $files = $_FILES['uploaded_files'];

    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $originalName = basename($files['name'][$i]);
            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $originalName);
            $targetPath = $uploadDir . $safeName;

            if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO vault_files (folder_id, filename, filepath) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $folderId, $originalName, $targetPath);
                $stmt->execute();
            }
        }
    }

    header("Location: ../dashboard.php?upload=success&folder_id=" . $folderId);

    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Upload Files - CyberVault</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: radial-gradient(circle at top, #1a0025, #000000);
      font-family: 'Courier New', monospace;
    }
    .neon-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(0, 255, 255, 0.2);
      box-shadow: 0 0 20px #00ffe7aa;
    }
    .neon-border {
      border: 1px solid #00ffe7;
      box-shadow: 0 0 5px #0ff, 0 0 10px #0ff;
    }
    .neon-button {
      background: linear-gradient(90deg, #00ffe7, #0099ff);
      color: black;
      font-weight: bold;
      box-shadow: 0 0 6px #00ffe7, 0 0 12px #00ffe7;
    }
    .neon-button:hover {
      background: linear-gradient(90deg, #00bfff, #006eff);
    }
    select, input[type="file"] {
      background-color: #000;
      color: #0ff;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col justify-center items-center px-4 py-10 text-[#00ffe7]">

  <main class="neon-card max-w-lg w-full rounded-xl shadow-lg p-8 md:p-10 neon-border">
    <h1 class="text-3xl font-extrabold text-center mb-8 tracking-tight text-[#00ffe7]">
      ⚡ Upload to Vault
    </h1>

    <form action="upload_files.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      <!-- Folder Select -->
      <div>
        <label for="folder_id" class="block text-sm font-semibold mb-2 text-[#00ffe7]">Select Folder</label>
        <select
          required
          name="folder_id"
          id="folder_id"
          class="block w-full rounded-md border border-cyan-500 py-3 px-4 focus:outline-none focus:ring-2 focus:ring-cyan-400 transition"
        >
          <option value="" selected>-- No Folder --</option>
          <?php
            $folders = $conn->query("SELECT id, name FROM vault_folders ORDER BY name ASC");
            while ($row = $folders->fetch_assoc()):
          ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- File Input -->
      <div>
        <label for="uploaded_files" class="block text-sm font-semibold mb-2 text-[#00ffe7]">Choose Files</label>
        <input
          type="file"
          name="uploaded_files[]"
          id="uploaded_files"
          multiple
          required
          class="block w-full rounded-md border border-cyan-500 py-3 px-4 cursor-pointer focus:outline-none focus:ring-2 focus:ring-cyan-400 transition"
        />
      </div>

      <!-- Buttons -->
      <div class="flex justify-between items-center mt-8">
        <a
          href="../dashboard.php"
          class="inline-flex items-center text-cyan-400 hover:text-white font-medium transition"
        >
          ⬅️ Back to Dashboard
        </a>
        <button
          type="submit"
          class="neon-button px-6 py-3 rounded-md transition text-sm"
        >
          Upload 🚀
        </button>
      </div>
    </form>
  </main>

</body>
</html>
