<?php
session_start();
require '../config/db.php';

require_once '../mailer/send_code.php';


$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $message = "All fields are required";
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare("SELECT id, fullname, username, password, email FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $fullname, $username, $hashedPassword, $email);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
    // Generate random 6-digit code
    $code = rand(100000, 999999);

    // Store temporarily in session
    $_SESSION['temp_user'] = [
        'id' => $id,
        'fullname' => $fullname,
        'username' => $username,
        'email' => $email,
        'code' => $code,
    ];

    // Send email
    require_once '../mailer/send_code.php'; // or wherever your `sendVerificationCode` function is
    if (sendVerificationCode($email, $code)) {
        header("Location: verify_code.php");
        exit();
    } else {
        $message = "Failed to send verification email.";
        $messageType = "error";
    }
}

            else {
                $message = "Password error";
                $messageType = 'error';
            }
        } else {
            $message = "User not found";
            $messageType = 'error';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CyberVault Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Orbitron', sans-serif;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background: black;
      color: #00ffcc;
    }

    canvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 0;
      pointer-events: none;
    }

    .login-glass {
      backdrop-filter: blur(20px);
      background: rgba(0, 0, 0, 0.7);
      border: 1px solid rgba(0, 255, 255, 0.15);
      box-shadow: 0 0 20px #00ffe7, 0 0 30px #00ff88;
      border-radius: 14px;
      z-index: 10;
    }

    .glow-title {
      font-size: 2rem;
      animation: glitch 1.5s infinite, flicker 1.3s infinite alternate;
      text-shadow: 0 0 10px #00ffe7, 0 0 20px #00ff88, 0 0 30px #00ffee;
    }

    .glow-input {
      background-color: black;
      border: 1px solid #00ffe7;
      color: #00ffcc;
      padding: 12px 16px;
      border-radius: 8px;
      width: 100%;
      font-family: monospace;
      transition: box-shadow 0.2s ease-in-out;
    }

    .glow-input:focus {
      outline: none;
      animation: neonPulse 1s infinite alternate;
    }

    .glow-button {
      background: linear-gradient(to right, #0077ff, #00ffe7);
      box-shadow: 0 0 10px #00ffe7, 0 0 20px #0077ff;
      color: black;
      font-weight: bold;
      transition: all 0.3s ease-in-out;
      padding: 12px;
      border-radius: 8px;
      width: 100%;
    }

    .glow-button:hover {
      transform: scale(1.05);
      box-shadow: 0 0 25px #00ffe7, 0 0 40px #0077ff;
    }

    @keyframes glitch {
      0% { text-shadow: 2px 0 lime, -2px 0 cyan; }
      20% { text-shadow: -2px 0 cyan, 2px 0 lime; }
      40% { text-shadow: 2px 0 green, -2px 0 magenta; }
      60% { text-shadow: 0px 0 cyan, 0px 0 yellow; }
      100% { text-shadow: 2px 0 lime, -2px 0 cyan; }
    }

    @keyframes neonPulse {
      from {
        box-shadow: 0 0 10px #00ffe7, 0 0 20px #00ff88;
      }
      to {
        box-shadow: 0 0 20px #00ffee, 0 0 30px #00ff88;
      }
    }

    @keyframes flicker {
      0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
        opacity: 1;
      }
      20%, 24%, 55% {
        opacity: 0.4;
      }
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen">

  <!-- Matrix Canvas -->
  <canvas id="matrixCanvas"></canvas>

  <!-- Login Glass Box -->
  <div class="login-glass w-full max-w-md px-4 py-8 sm:px-8 text-center relative z-10">

    <h1 class="glow-title mb-6 tracking-widest">💾 CYBERVAULT</h1>

    <?php if (!empty($message)): ?>
      <div class="mb-4 p-2 rounded bg-red-700 text-sm text-white">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
      <input type="text" name="username" placeholder="👤 USERNAME" class="glow-input" required />
      <input type="password" name="password" placeholder="🔐 PASSWORD" class="glow-input" required />
      <button type="submit" class="glow-button">🟢 ACCESS VAULT</button>
    </form>
  </div>

  <!-- Synth Sound Effect -->
  <audio id="click-sound" src="assets/synth-click.mp3" preload="auto"></audio>
  <script>
    document.querySelector("form").addEventListener("submit", () => {
      document.getElementById("click-sound").play();
    });
  </script>

  <!-- Matrix JS -->
  <script>
    const canvas = document.getElementById("matrixCanvas");
    const ctx = canvas.getContext("2d");
    canvas.height = window.innerHeight;
    canvas.width = window.innerWidth;

    const matrix = "アカサタナハマヤラワ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const letters = matrix.split("");
    const fontSize = 16;
    const columns = canvas.width / fontSize;
    const drops = [];

    for (let x = 0; x < columns; x++) {
      drops[x] = 1;
    }

    function draw() {
      ctx.fillStyle = "rgba(0, 0, 0, 0.1)";
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      ctx.fillStyle = "#00ffcc";
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

    function resizeCanvas() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  const newColumns = Math.floor(canvas.width / fontSize);
  drops.length = 0; // reset
  for (let i = 0; i < newColumns; i++) {
    drops[i] = 1;
  }
}
window.addEventListener("resize", resizeCanvas);
resizeCanvas(); // run on load

  </script>
</body>
</html>
