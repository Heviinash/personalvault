<?php
require '../config/db.php';

if (!isset($_GET['file_id'])) {
    http_response_code(400);
    echo "Missing file ID.";
    exit;
}

$fileId = intval($_GET['file_id']);
$stmt = $conn->prepare("SELECT filename, filepath FROM vault_files WHERE id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

$file = $result->fetch_assoc();
$fullPath = $file['filepath'];

if (!file_exists($fullPath)) {
    http_response_code(404);
    echo "File does not exist.";
    exit;
}

$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mime = mime_content_type($fullPath);

// Extensions safe to show inline with raw browser rendering
$browserViewable = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'docx'];

if (in_array($ext, $browserViewable)) {
    header("Content-Type: $mime");
    header("Content-Disposition: inline; filename=\"" . basename($file['filename']) . "\"");
    header("Content-Length: " . filesize($fullPath));
    readfile($fullPath);
    exit;
}

// Otherwise, render with cyberpunk view for text-based or unsupported files
$content = file_get_contents($fullPath);
$escapedContent = htmlspecialchars($content);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View <?= htmlspecialchars($file['filename']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #0f0f1a;
      color: #d1d1ff;
      font-family: monospace;
    }
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .neon-border {
      border: 2px solid #00fff7;
    }
    pre {
      white-space: pre-wrap;
      word-wrap: break-word;
    }
  </style>
</head>

<body class="relative z-10 min-h-screen flex items-center justify-center p-8">

<canvas id="matrixCanvas" style="position: fixed; top: 0; left: 0; z-index: 0; pointer-events: none;"></canvas>

<div class="glass neon-border p-6 rounded-lg max-w-4xl w-full shadow-xl z-10">
  <h1 class="text-2xl font-bold text-pink-300 mb-4"><?= htmlspecialchars($file['filename']) ?></h1>
  <pre class="text-sm text-green-300 bg-black p-4 rounded overflow-x-auto max-h-[80vh]"><?= $escapedContent ?></pre>
</div>

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
    ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#00ffaa";
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
