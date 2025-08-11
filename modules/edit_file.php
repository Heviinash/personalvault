<?php
session_start();
require '../config/db.php';

if (!isset($_GET['file_id'])) {
    die("Missing file ID");
}

$fileId = intval($_GET['file_id']);
$stmt = $conn->prepare("SELECT * FROM vault_files WHERE id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if (!$file || !file_exists($file['filepath'])) {
    die("File not found");
}

// Only allow editable text files (simple check)
$allowedExtensions = ['txt', 'html', 'js', 'css', 'php', 'md'];
$ext = pathinfo($file['filename'], PATHINFO_EXTENSION);
if (!in_array(strtolower($ext), $allowedExtensions)) {
    die("This file type cannot be edited");
}

$content = file_get_contents($file['filepath']);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit File - <?= htmlspecialchars($file['filename']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-green-400 min-h-screen px-6 py-10 font-mono">
  <canvas id="matrixCanvas" class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none"></canvas>

  <main class="relative z-10 max-w-4xl mx-auto bg-[#0f0f0f] border border-green-400/20 rounded-xl shadow-lg p-4 md:p-8">

  <h1 class="text-xl sm:text-2xl font-bold text-green-400 mb-6 tracking-wide break-words">
    📝 Edit File: <span class="text-blue-400"><?= htmlspecialchars($file['filename']) ?></span>
  </h1>

  <form method="POST" action="save_file_content.php" class="space-y-6">
    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">

    <div class="overflow-x-auto">
      <textarea name="content" rows="20"
        class="w-full bg-black border border-green-500 text-green-300 p-4 rounded-md font-mono text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 transition shadow-inner whitespace-pre">
<?= htmlspecialchars($content) ?>
      </textarea>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
      <button type="submit"
        class="inline-flex items-center bg-cyan-500 hover:bg-cyan-600 text-black font-bold px-6 py-2 rounded shadow-md transition">
        💾 Save Changes
      </button>

      <a href="../dashboard.php"
        class="text-sm text-green-300 hover:text-cyan-400 transition underline underline-offset-2">
        ← Cancel & Go Back
      </a>
    </div>
  </form>
</main>


<script>
  const canvas = document.getElementById("matrixCanvas");
  const ctx = canvas.getContext("2d");

  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  const letters = "01日月火水木金土アァイィウエオカキクケコサシスセソ0123456789".split("");
  const fontSize = 14;
  const columns = Math.floor(canvas.width / fontSize);
  const drops = Array(columns).fill(1);

  function draw() {
    ctx.fillStyle = "rgba(0, 0, 0, 0.1)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#00ffcc"; // bluish-green
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

  window.addEventListener("resize", () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  });
</script>


</body>
</html>
