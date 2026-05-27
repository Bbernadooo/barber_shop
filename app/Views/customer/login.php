<?php /* Variables expected: $error */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login | Barber Shop</title>
    <link rel="stylesheet" href="/Barber_shop/public/css/style.css">
    <style>
        :root { --accent: #ff2e2e; }
        body {
            background: #111;
            color: #fff;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .auth-card {
            background: #161616;
            border: 1px solid #222;
            border-radius: 8px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
        }
        .auth-card h2 {
            margin: 0 0 0.25rem;
            font-size: 1.75rem;
            letter-spacing: 1px;
        }
        .auth-card p.subtitle {
            color: #888;
            font-size: 0.9rem;
            margin: 0 0 1.75rem;
        }
        .auth-card h2 span { color: var(--accent); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #aaa;
            margin-bottom: 0.5rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: #111;
            border: 1px solid #2a2a2a;
            color: #fff;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 0.95rem;
        }
        .form-control:focus { border-color: var(--accent); outline: none; }
        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .btn-submit:hover { background: #cc2424; }
        .error-msg {
            background: rgba(255,46,46,0.1);
            color: var(--accent);
            padding: 0.75rem;
            border-left: 3px solid var(--accent);
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
            border-radius: 2px;
        }
        .auth-switch {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #888;
        }
        .auth-switch a { color: var(--accent); text-decoration: none; }
        .auth-switch a:hover { text-decoration: underline; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #555;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Welcome <span>Back</span></h2>
        <p class="subtitle">Login to access your booking history and preferences.</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required autocomplete="email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-submit">Login</button>
        </form>

        <div class="auth-switch">
            Don't have an account?
            <a href="/Barber_shop/public/index.php?page=customer-signup">Sign up here</a>
        </div>
        <a href="/Barber_shop/public/booking.html" class="back-link">← Back to booking</a>
    </div>
</body>
</html>