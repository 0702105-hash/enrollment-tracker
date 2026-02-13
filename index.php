<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Tracker - Login</title>
    <style>
        body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f5f7fa; }
        .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 350px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background: #45a049; }
        h2 { text-align: center; margin-bottom: 30px; color: #333; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Enrollment Tracker</h2>
        <form action="dashboard.php" method="post">
            <input type="password" placeholder="Enter password (admin)" name="password" required>
            <button type="submit">Login → Dashboard</button>
        </form>
        <?php if (isset($_POST['password']) && $_POST['password'] === 'admin123'): ?>
            <script>document.location = 'dashboard.php';</script>
        <?php endif; ?>
    </div>
</body>
</html>
