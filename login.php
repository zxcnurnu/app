<?php
// login.php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    // Попытка входа как пользователь
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: dashboard.php");
        exit;
    }
    
    // Попытка входа как администратор
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = ?");
    $stmt->execute([$login]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        header("Location: admin.php");
        exit;
    }
    
    $error = "Неверный логин или пароль";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | СпортGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #4CAF50;
            --accent: #FF9800;
            --dark: #2c3e50;
            --light: #f5f5f5;
            --danger: #e74c3c;
            --success: #27ae60;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .container {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid var(--accent);
            position: relative;
        }
        
        .sports-bg {
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100"><circle cx="50" cy="50" r="10" fill="%233498db" opacity="0.2"/></svg>');
            background-size: 200px;
            padding: 30px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }
        
        .header h1 {
            color: var(--dark);
            font-size: 28px;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .header h1::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo i {
            font-size: 36px;
            color: var(--primary);
            background: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border: 2px solid var(--accent);
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 18px;
        }
        
        input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        .error {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--danger);
            font-size: 14px;
        }
        
        .error p {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .error p:last-child {
            margin-bottom: 0;
        }
        
        .error i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .success {
            color: var(--success);
            background: rgba(39, 174, 96, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--success);
            font-size: 14px;
        }
        
        .success p {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .success i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .register-link p {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .register-link a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        .sports-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            opacity: 0.7;
        }
        
        .sports-icons i {
            font-size: 24px;
            color: var(--primary);
        }
        
        /* Мобильная адаптация */
        @media (max-width: 576px) {
            .container {
                border-radius: 10px;
            }
            
            .sports-bg {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            input {
                padding: 12px 12px 12px 40px;
            }
            
            button {
                padding: 14px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 400px) {
            .sports-bg {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sports-bg">
            <div class="logo">
                <i class="fa-solid fa-person-running"></i>
            </div>
            
            <div class="header">
                <h1>Вход в систему</h1>
                <p>Вернись к своим спортивным достижениям!</p>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success">
                    <p><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error">
                    <p><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="login">Логин:</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="login" id="login" required placeholder="Введите ваш логин">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" required placeholder="Введите ваш пароль">
                    </div>
                </div>
                
                <button type="submit">Войти</button>
            </form>
            
            <div class="register-link">
                <p>Нет аккаунта? <a href="index.php">Зарегистрироваться</a></p>
            </div>
            
            <div class="sports-icons">
                <i class="fas fa-football-ball"></i>
                <i class="fas fa-basketball-ball"></i>
                <i class="fas fa-baseball-ball"></i>
                <i class="fas fa-volleyball-ball"></i>
                <i class="fas fa-running"></i>
            </div>
        </div>
    </div>
</body>
</html>