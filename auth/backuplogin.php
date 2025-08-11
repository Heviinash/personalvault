<?php

  session_start();

  require '../config/db.php';

  $message = '';
  $messageType = '';

  if ($_SERVER['REQUEST_METHOD']==='POST')
  {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // First check whether fields are blank
    
    if(empty($username) || empty($password))
    { 

      $message = "All fields are required";
      $messageType = 'error';

    }

    // Then proceed fetching data based on the username

    else 
    {

      $stmt = $conn->prepare("SELECT id, fullname, username, password FROM users WHERE username = ?");
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $stmt->store_result();

      if($stmt->num_rows === 1)
      {
        $stmt->bind_result($id, $fullname, $username, $hashedPassword);
        $stmt->fetch();

        //compare the password from the field with the hashed password from the database

        if (password_verify($password, $hashedPassword))
        {
             $_SESSION['id'] = $id;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            header("Location: ../dashboard.php");
            exit();
        }

        else
        {
          $message = "Password error";
          $messageType = 'error';
        }


      }

      else 
      {
        $message = "User not found";
        $messageType = 'error';
      }

    }

    $stmt->close();



  }


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CyberVault Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet"/>
  <style>
    body {
      font-family: 'Orbitron', sans-serif;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background: black;
      color: #00ffcc;
    }

    /* Matrix Rain Effect */
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
      animation: glitch 1.5s infinite;
      text-shadow: 0 0 10px #00ffe7, 0 0 20px #00ff88, 0 0 30px #00ffee;
    }

    .glow-input:focus {
      outline: none;
      border-color: #00ffe7;
      box-shadow: 0 0 10px #00ffe7;
    }

    .glow-button {
      background: linear-gradient(to right, #0077ff, #00ffe7);
      box-shadow: 0 0 10px #00ffe7, 0 0 20px #0077ff;
      color: black;
      font-weight: bold;
      transition: all 0.3s ease-in-out;
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
  </style>
</head>

<body class="flex items-center justify-center min-h-screen">

  <!-- Matrix Canvas -->
  <canvas id="matrixCanvas"></canvas>

  <!-- Login Form -->
  <div class="login-glass w-full max-w-md p-8 text-center relative z-10">
    <h1 class="glow-title mb-6 tracking-widest">💾 CYBERVAULT</h1>

    <?php if (!empty($message)): ?>
      <div class="mb-4 p-2 rounded bg-red-700 text-sm text-white">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
  <input
    type="text"
    name="username"
    placeholder="👤 USERNAME"
    class="w-full px-4 py-3 bg-black border border-lime-400 text-lime-300 font-mono rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-400 shadow-lg shadow-lime-300/10"
    required
  />
  <input
    type="password"
    name="password"
    placeholder="🔐 PASSWORD"
    class="w-full px-4 py-3 bg-black border border-lime-400 text-lime-300 font-mono rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-400 shadow-lg shadow-lime-300/10"
    required
  />
  <button
    type="submit"
    class="w-full py-3 bg-gradient-to-r from-lime-400 to-green-500 text-black font-bold rounded-lg shadow-lg hover:scale-105 transition-transform duration-300"
  >
    🟢 ACCESS VAULT
  </button>
</form>





  </div>

  <!-- Matrix JS Effect -->
  <script>
    const canvas = document.getElementById("matrixCanvas");
    const ctx = canvas.getContext("2d");

    // set canvas full screen
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
      ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
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

    // Handle window resize
    window.addEventListener("resize", () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
  </script>

</body>
</html>


