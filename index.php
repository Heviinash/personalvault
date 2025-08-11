<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CyberVault - Entry</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet">

  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
      font-family: 'Orbitron', monospace;
      background: black;
    }

    canvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 0;
    }

    .content {
      position: relative;
      z-index: 1;
    }

    .glow-btn {
      background: linear-gradient(90deg, #00ffe7, #0099ff);
      color: black;
      font-weight: bold;
      font-size: 1.2rem;
      padding: 0.75rem 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 0 10px #00ffe7, 0 0 20px #00ffe7;
      transition: 0.3s ease-in-out;
    }

    .glow-btn:hover {
      background: linear-gradient(90deg, #00ccff, #0066ff);
      box-shadow: 0 0 15px #00ccff, 0 0 30px #00ccff;
    }
  </style>
</head>
<body>
  <canvas id="matrixCanvas"></canvas>

  <div class="content flex items-center justify-center min-h-screen">
    <a href="auth/login.php" class="glow-btn">🔓 Enter CyberVault</a>
  </div>

  <script>
    const canvas = document.getElementById("matrixCanvas");
    const ctx = canvas.getContext("2d");

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const letters = "アァイィウエエオカガキギクグケゲコゴサザシジスズセゼソゾタダチッヂヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモヤユヨラリルレロワン0123456789".split("");
    const fontSize = 16;
    const columns = Math.floor(canvas.width / fontSize);
    const drops = Array.from({ length: columns }).fill(1);

    function draw() {
      ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      ctx.fillStyle = "#00FFAA";
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
