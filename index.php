<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Tracker - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fb 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Banner */
        .header-banner {
            background: linear-gradient(135deg, #2f855a 0%, #2c5282 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-banner h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header-banner p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 300;
        }

        /* Main Container */
        .login-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            padding: 50px 40px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card h2 {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 12px;
            text-align: center;
            font-weight: 600;
        }

        .login-card .subtitle {
            text-align: center;
            color: #718096;
            font-size: 14px;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            color: #2d3748;
        }

        input[type="password"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #2c5282;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.1);
            background-color: #f8fafd;
        }

        button {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #2f855a 0%, #2c5282 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            box-shadow: 0 4px 15px rgba(44, 82, 130, 0.25);
            letter-spacing: 0.3px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 82, 130, 0.35);
        }

        button:active {
            transform: translateY(0);
        }

        .info-section {
            margin-top: 28px;
            padding-top: 28px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 13px;
            color: #718096;
        }

        .info-section strong {
            color: #2d3748;
            font-weight: 600;
        }

        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c53030;
        }

        .success {
            background: #c6f6d5;
            color: #22543d;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #22543d;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 24px;
            }

            .header-banner {
                padding: 30px 20px;
            }

            .header-banner h1 {
                font-size: 26px;
            }

            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Banner -->
    <div class="header-banner">
        <h1>📚 Enrollment Tracker</h1>
        <p>Student Enrollment Management System</p>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <h2>Welcome Back</h2>
            <p class="subtitle">Please log in to access the dashboard</p>

            <form method="post" action="dashboard.php">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Enter your password" 
                        required 
                        autofocus
                    >
                </div>

                <button type="submit">🔓 Sign In</button>
            </form>

            <div class="info-section">
                <strong>Demo Credentials</strong><br>
                Password: <strong>admin123</strong>
            </div>
        </div>
    </div>
</body>
</html>
