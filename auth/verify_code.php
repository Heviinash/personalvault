<?php
session_start();

if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}

$submittedCode = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel'])) {
        header("Location: login.php");
        exit();
    }

    $submittedCode = trim($_POST['code']);
    $actualCode = $_SESSION['temp_user']['code'];

    if ($submittedCode === (string)$actualCode) {
        $_SESSION['id'] = $_SESSION['temp_user']['id'];
        $_SESSION['fullname'] = $_SESSION['temp_user']['fullname'];
        $_SESSION['username'] = $_SESSION['temp_user']['username'];

        unset($_SESSION['temp_user']);
        header("Location: ../dashboard.php");
        exit();
    } else {
        $error = "Invalid code";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Orbitron', sans-serif;
            overflow: hidden;
        }
        canvas {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 0;
        }
        .form-container {
            z-index: 10;
        }
    </style>
</head>
<body class="bg-black text-green-400 flex items-center justify-center min-h-screen relative">

    <!-- Matrix Rain Canvas -->
    <canvas id="matrix"></canvas>

    <!-- Verification Form -->
    <form method="POST" class="form-container p-8 rounded-lg shadow-lg border border-cyan-400 max-w-md w-full bg-gray-900 bg-opacity-80">
        <h2 class="text-2xl font-bold mb-4 text-center">🔐 Enter Verification Code</h2>

        <?php if (!empty($error)): ?>
            <p class="mb-4 text-red-400 text-sm"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <input 
            type="text" 
            name="code" 
            placeholder="6-digit code from email" 
            class="w-full mb-4 p-2 bg-black border border-green-500 rounded text-green-400 placeholder-green-300" 
            required
        >

        <div class="flex gap-4">
            <button 
                type="submit" 
                class="w-1/2 bg-green-500 hover:bg-green-700 text-black font-bold py-2 rounded"
            >
                ✅ Verify
            </button>

            <button 
                type="submit" 
                name="cancel" 
                class="w-1/2 bg-red-500 hover:bg-red-700 text-black font-bold py-2 rounded"
            >
                ❌ Cancel
            </button>
        </div>
    </form>

    <!-- Matrix Rain Script -->
    <script>
    const canvas = document.getElementById('matrix');
    const ctx = canvas.getContext('2d');

    canvas.height = window.innerHeight;
    canvas.width = window.innerWidth;

    // More Matrix-style character set
    const matrixChars = "アァイィウエエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲンABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*()".split("");
    const fontSize = 16;
    const columns = Math.floor(canvas.width / fontSize);
    const drops = Array(columns).fill(1);

    function drawMatrix() {
        ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = "#0F0";
        ctx.font = fontSize + "px monospace";

        for (let i = 0; i < drops.length; i++) {
            const char = matrixChars[Math.floor(Math.random() * matrixChars.length)];
            ctx.fillText(char, i * fontSize, drops[i] * fontSize);

            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }

            drops[i]++;
        }
    }

    setInterval(drawMatrix, 33);

    window.addEventListener("resize", () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
</script>

</body>
</html>
