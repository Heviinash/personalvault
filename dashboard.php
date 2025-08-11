<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: auth/login.php");
    exit();
}

require 'config/db.php';
$selectedFolderId = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : null;

$selectedFolderName = null;
if ($selectedFolderId) {
    $stmt = $conn->prepare("SELECT name FROM vault_folders WHERE id = ?");
    $stmt->bind_param("i", $selectedFolderId);
    $stmt->execute();
    $stmt->bind_result($selectedFolderName);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cybervault-Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    body {
      background-color: #0f0f1a;
      color: #d1d1ff;
    }
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .neon-btn {
      background: linear-gradient(to right, #0ff, #0f0);
      color: #000;
      font-weight: bold;
    }
    .neon-btn:hover {
      background: linear-gradient(to right, #f0f, #0ff);
    }
    .neon-border {
      border: 2px solid #00fff7;
    }
  </style>
</head>

<body class="font-mono">

<canvas id="matrixCanvas" style="position: fixed; top: 0; left: 0; z-index: 0; pointer-events: none;"></canvas>

<!---<div class="flex min-h-screen"> --->
<div style="position: relative; z-index: 10;" class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 p-4 hidden md:block glass">
    <h2 class="text-2xl font-bold text-pink-400 mb-6">💽 CyberVault</h2>

    <button onclick="toggleModal(true)"
            class="neon-btn w-full py-2 rounded mb-4">
      ➕ New Folder
    </button>

    <a href="dashboard.php"
       class="block py-2 px-3 mb-4 rounded hover:bg-pink-800 bg-opacity-20 bg-pink-600 text-white font-semibold">
      🏠 Dashboard
    </a>

    <?php
    $folders = $conn->query("SELECT * FROM vault_folders ORDER BY name ASC");
    while ($row = $folders->fetch_assoc()):
    ?>
    <div class="flex justify-between items-center hover:bg-pink-900 hover:bg-opacity-10 px-2 py-1 rounded">
      <a href="?folder_id=<?= $row['id'] ?>" class="truncate max-w-[80%] text-cyan-300">📁 <?= htmlspecialchars($row['name']) ?></a>
      <button onclick="openDeleteFolderModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')" class="text-red-400 hover:text-red-600">🗑️</button>
    </div>
    <?php endwhile; ?>
  </aside>


      <!-- Hamburger Menu Button (mobile only) -->
  <button onclick="toggleMobileSidebar()" class="md:hidden text-pink-400 text-xl p-2 fixed top-4 left-4 z-50 bg-gray-900 border border-pink-500 rounded">
    ☰ Menu
  </button>

  <!-- Mobile Sidebar (initially hidden) -->
<aside id="mobileSidebar" class="fixed top-0 left-0 w-64 h-full bg-gray-900 text-white p-4 z-40 hidden overflow-y-auto border-r border-pink-400 glass">
  <h2 class="text-2xl font-bold text-pink-400 mb-6">💽 CyberVault</h2>

  <button onclick="toggleModal(true); toggleMobileSidebar()" class="neon-btn w-full py-2 rounded mb-4">➕ New Folder</button>

  <a href="dashboard.php" class="block py-2 px-3 mb-4 rounded hover:bg-pink-800 bg-opacity-20 bg-pink-600 text-white font-semibold">🏠 Dashboard</a>

  <?php
  $folders = $conn->query("SELECT * FROM vault_folders ORDER BY name ASC");
  while ($row = $folders->fetch_assoc()):
  ?>
    <div class="flex justify-between items-center hover:bg-pink-900 hover:bg-opacity-10 px-2 py-1 rounded">
      <a href="?folder_id=<?= $row['id'] ?>" class="truncate max-w-[80%] text-cyan-300" onclick="toggleMobileSidebar()">📁 <?= htmlspecialchars($row['name']) ?></a>
      <button onclick="openDeleteFolderModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>'); toggleMobileSidebar()" class="text-red-400 hover:text-red-600">🗑️</button>
    </div>
  <?php endwhile; ?>
</aside>


  <!-- Main Content -->
  <!-- Main Content -->
<div class="flex-1 flex flex-col pt-16 md:pt-0">


    <!-- Top Bar -->
<header class="glass border-b px-4 py-3 flex flex-wrap items-center justify-between gap-3">
  <!-- Upload button -->
  <a href="modules/upload_files.php"
     class="neon-btn px-4 py-2 rounded text-center">📤 Upload</a>

  <!-- Logout button -->
  <a href="auth/logout.php"
     class="bg-gray-800 px-4 py-2 text-red-400 hover:text-red-200 rounded text-center">🚪 Logout</a>
</header>

    



    <!-- Alerts -->
    <main class="p-6 flex-1 overflow-y-auto">


      <?php
        if ($selectedFolderId) {
            $stmt = $conn->prepare("SELECT f.*, d.name as folder_name FROM vault_files f LEFT JOIN vault_folders d ON f.folder_id = d.id WHERE f.folder_id = ? ORDER BY f.uploaded_at DESC");
            $stmt->bind_param("i", $selectedFolderId);
            $stmt->execute();
            $files = $stmt->get_result();
        } else {
          $files = false;
        }
      ?>



      <?php foreach (['flash_message' => 'green', 'error_flash_message' => 'red', 'success_flash_message' => 'green'] as $key => $color): ?>
        <?php if (isset($_SESSION[$key])): ?>
          <div class="mb-4 px-4 py-2 rounded bg-<?= $color ?>-100 text-<?= $color ?>-800 border border-<?= $color ?>-300">
            <?= $_SESSION[$key] ?>
          </div>
          <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if ($selectedFolderId): ?>
        <h2 class="text-2xl font-semibold text-cyan-300 mb-4">🗂️ <?= htmlspecialchars($selectedFolderName) ?></h2>

      <?php else: ?>
        <h2 class="text-xl font-semibold text-red-400 mb-4">🛑 Select a folder to view files.</h2>
      <?php endif; ?>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
  <?php if ($files && $files->num_rows > 0): ?>
    <?php while ($file = $files->fetch_assoc()): ?>
      <div class="glass p-4 rounded-lg border border-cyan-500 shadow-xl flex flex-col justify-between">
        <h3 class="text-base sm:text-lg font-bold break-words text-pink-200"><?= htmlspecialchars($file['filename']) ?></h3>
        <p class="text-sm text-gray-300 mt-1">
          📁 <?= $file['folder_name'] ?><br>
          🕒 <?= date("d M Y, H:i", strtotime($file['uploaded_at'])) ?>
        </p>

        <?php
          $allowedExtensions = ['txt', 'html', 'js', 'css', 'php', 'md'];
          $ext = pathinfo($file['filename'], PATHINFO_EXTENSION);
          $isEditable = in_array(strtolower($ext), $allowedExtensions);
        ?>

        <!-- Button Row -->
        <div class="flex flex-wrap gap-2 mt-4">
          <!-- Edit -->
          <?php if ($isEditable): ?>
            <a href="modules/edit_file.php?file_id=<?= $file['id'] ?>"
              class="inline-flex items-center px-3 py-1.5 bg-[#00f0ff] text-black text-sm font-bold rounded border border-cyan-400 hover:bg-[#00d3e6] transition duration-200 shadow-[0_0_8px_#00f0ff] hover:shadow-cyan-300/80">
              ✏️ Edit
            </a>
          <?php else: ?>
            <span class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-gray-500 text-sm font-bold rounded border border-gray-600 cursor-not-allowed shadow-[0_0_5px_#444]">
              ✏️ Edit
            </span>
          <?php endif; ?>

          <!-- View -->
          <a href="modules/view_file.php?file_id=<?= $file['id'] ?>"
            target="_blank"
            class="inline-flex items-center px-3 py-1.5 bg-[#0f0] text-black text-sm font-bold rounded border border-lime-400 hover:bg-[#14ff14] transition duration-200 shadow-[0_0_8px_#0f0] hover:shadow-lime-300/80">
            👁️ View
          </a>

          <!-- Download -->
          <a href="<?= str_replace('../', '', htmlspecialchars($file['filepath'])) ?>" 
            download 
            class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-sm font-bold rounded border border-blue-400 hover:bg-blue-600 transition duration-200 shadow-[0_0_8px_#3b82f6] hover:shadow-blue-300/80">
            ⬇️ Download
          </a>

          <!-- Rename -->
          <button onclick="openModal(<?= $file['id'] ?>, '<?= htmlspecialchars($file['filename'], ENT_QUOTES) ?>')"
            class="inline-flex items-center px-3 py-1.5 bg-[#f0e130] text-black text-sm font-bold rounded border border-yellow-400 hover:bg-[#fcea57] transition duration-200 shadow-[0_0_8px_#f0e130] hover:shadow-yellow-300/80">
            ✏️ Rename
          </button>

          <!-- Delete -->
          <button onclick="openDeleteModal(<?= $file['id'] ?>, <?= $selectedFolderId ?>)"
            class="inline-flex items-center px-3 py-1.5 bg-[#ff005d] text-white text-sm font-bold rounded border border-pink-500 hover:bg-[#ff2d7d] transition duration-200 shadow-[0_0_8px_#ff005d] hover:shadow-pink-300/80">
            🗑️ Delete
          </button>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="col-span-full text-center text-gray-400">📂 No files in this folder yet.</div>
  <?php endif; ?>
</div>

    </main>
  </div>
</div>

<!-- Modals (Create, Edit, Delete File & Folder) -->
<!-- 💾 Folder Create Modal -->
<div id="folderModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="glass p-6 rounded max-w-md w-full">
    <h2 class="text-xl font-bold text-cyan-300 mb-4">📁 Create New Folder</h2>
    <form action="modules/create_folder.php" method="POST">
      <input name="folder_name" placeholder="Folder Name" required class="w-full px-4 py-2 bg-black text-white border border-cyan-400 rounded mb-4">
      <div class="flex justify-end gap-2">
        <button type="button" onclick="toggleModal(false)" class="px-4 py-2 bg-gray-800 text-white rounded">Cancel</button>
        <button type="submit" class="neon-btn px-4 py-2 rounded">Create</button>
      </div>
    </form>
  </div>
</div>

<!-- 🛠️ Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-70 hidden flex justify-center items-center z-50">
  <div class="glass p-6 rounded-lg max-w-md w-full">
    <h2 class="text-xl font-bold text-yellow-300 mb-4">Edit Filename</h2>
    <form method="POST" action="modules/update_filename.php">
      <input type="hidden" name="file_id" id="modalFileId">
      <input type="hidden" name="folder_id" value="<?= $selectedFolderId ?>">
      <input type="text" name="filename" id="modalFilename" class="w-full p-2 bg-black text-white border border-yellow-400 rounded mb-4">
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-800 text-white rounded">Cancel</button>
        <button type="submit" class="neon-btn px-4 py-2 rounded">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- 🗑️ Delete File Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center z-50">
  <div class="glass p-6 rounded-lg w-full max-w-md">
    <h2 class="text-lg font-bold text-red-400 mb-4">Confirm File Deletion</h2>
    <p class="text-white mb-4">Are you sure? This cannot be undone.</p>
    <form method="POST" action="modules/delete_file.php">
      <input type="hidden" name="file_id" id="deleteFileId">
      <input type="hidden" name="folder_id" id="deleteFolderId">
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-800 text-white rounded">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- 🗂️ Delete Folder Modal -->
<div id="deleteFolderModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
  <div class="glass p-6 rounded-lg max-w-md w-full">
    <h3 class="text-xl font-bold text-red-500 mb-4">Delete Folder</h3>
    <p class="mb-4">Are you sure you want to delete folder <span id="deleteFolderName" class="text-red-300 font-semibold"></span> and all its files?</p>
    <form method="POST" action="modules/delete_folder.php">
      <input type="hidden" name="folder_id" id="delete-folder-id">
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeDeleteFolderModal()" class="px-4 py-2 bg-gray-700 text-white rounded">Cancel</button>
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Yes, Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- JS -->
<script>
  function toggleModal(show) {
    const modal = document.getElementById("folderModal");
    modal.classList.toggle("hidden", !show);
  }

  function openModal(fileId, filename) {
    document.getElementById('modalFileId').value = fileId;
    document.getElementById('modalFilename').value = filename;
    document.getElementById('editModal').classList.remove('hidden');
  }

  function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
  }

  function openDeleteModal(fileId, folderId) {
    document.getElementById('deleteFileId').value = fileId;
    document.getElementById('deleteFolderId').value = folderId;
    document.getElementById('deleteModal').classList.remove('hidden');
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
  }

  function openDeleteFolderModal(folderId, folderName) {
    document.getElementById('delete-folder-id').value = folderId;
    document.getElementById('deleteFolderName').textContent = folderName;
    document.getElementById('deleteFolderModal').classList.remove('hidden');
  }

  function closeDeleteFolderModal() {
    document.getElementById('deleteFolderModal').classList.add('hidden');
  }
</script>

<script>
function toggleMobileSidebar() {
  const sidebar = document.getElementById('mobileSidebar');
  sidebar.classList.toggle('hidden');
}
</script>


<script>
  const canvas = document.getElementById("matrixCanvas");
  const ctx = canvas.getContext("2d");

  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  const letters = "アァイィウエエオカガキギクグケゲコゴサザシジスズセゼソゾタダチッヂヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモヤユヨラリルレロワン0123456789".split("");
  const fontSize = 16;
  const columns = canvas.width / fontSize;
  const drops = Array.from({ length: columns }, () => 1);

  function draw() {
    ctx.fillStyle = "rgba(0, 0, 0, 0.1)"; // was 0.1, now 0.05 = slower fade
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#00ffaa"; // from "#00FFAA"

    ctx.font = fontSize + "px monospace";

    for (let i = 0; i < drops.length; i++) {
      const text = letters[Math.floor(Math.random() * letters.length)];
      ctx.fillText(text, i * fontSize, drops[i] * fontSize);

      if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
        drops[i] = 0;
      }
      drops[i]++;
    }
  }

  setInterval(draw, 33);
</script>







</body>
</html>
