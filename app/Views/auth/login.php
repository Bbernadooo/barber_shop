<?php
/**
 * Variables expected: $error (string)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | Premium Barber Shop</title>
    <link rel="stylesheet" href="/Barber_shop/public/css/staff-style.css">
    <style>
        :root {
            --primary: #111111;
            --secondary: #161616;
            --accent: #ff2e2e;
            --border: #222222;
        }
        body {
            background-color: var(--primary);
            color: #fff;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: var(--secondary);
            border: 1px solid var(--border);
            padding: 2.5rem;
            border-radius: 6px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .login-card h2 {
            margin-top: 0;
            font-size: 1.75rem;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #aaa;
            text-transform: uppercase;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: #111;
            border: 1px solid var(--border);
            color: #fff;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-control:focus { border-color: var(--accent); outline: none; }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
        }
        .btn-login:hover { background: #cc2424; }
        .error-msg {
            background: rgba(255, 46, 46, 0.1);
            color: var(--accent);
            padding: 0.75rem;
            border-left: 3px solid var(--accent);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>CONSOLE LOGIN</h2>
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username / Staff Identity</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Secure Key Passphrase</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">Verify Console Identity</button>
        </form>
    </div>
</body>
</html>