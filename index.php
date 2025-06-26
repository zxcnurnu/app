<?php
// register.php
include 'config.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $login = trim($_POST['login']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $errors = [];
    
    // Валидация ФИО
    if (empty($full_name)) {
        $errors[] = "ФИО обязательно";
    } elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-\.']+$/u", $full_name)) {
        $errors[] = "ФИО может содержать только буквы, пробелы, дефисы и апострофы";
    }
    
    
    // Валидация email
    if (!validate_email($email)) {
        $errors[] = "Неверный формат email";
    }
    
    // Валидация пароля
    $pass_length = strlen($_POST['password']);
    if ($pass_length < 8 || $pass_length > 20) {
        $errors[] = "Пароль должен быть от 8 до 20 символов";
    }
    
    // Проверка уникальности
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR login = ?");
    $stmt->execute([$email, $login]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Email или логин уже используются";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, phone, email, login, password) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$full_name, $phone, $email, $login, $password]);
        
        $_SESSION['success'] = "Регистрация прошла успешно! Теперь войдите в систему.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | СпортGo</title>
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
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            background: #eee;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s;
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
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .login-link p {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .login-link a:hover {
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
                <h1>Регистрация</h1>
                <p>Присоединяйся к спортивному сообществу!</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="full_name">ФИО:</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" id="full_name" required 
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                               placeholder="Иванов Иван Иванович">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон:</label>
                    <div class="input-icon">
                        <i class="fas fa-mobile-alt"></i>
                        <input type="tel" name="phone" id="phone" required 
                               placeholder="+791234567893">
                    </div>
                    <small>Формат: +791234567893</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="example@mail.ru">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="login">Логин:</label>
                    <div class="input-icon">
                        <i class="fas fa-user-tag"></i>
                        <input type="text" name="login" id="login" required 
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               placeholder="Придумайте логин">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль (8-20 символов):</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" required 
                               minlength="8" maxlength="20"
                               placeholder="Не менее 8 символов">
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter" id="strengthMeter"></div>
                    </div>
                </div>
                
                <button type="submit">Зарегистрироваться</button>
            </form>
            
            <div class="login-link">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
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

    <script>
        // Валидация пароля в реальном времени
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9!@#$%^&*]/.test(password)) strength += 25;
            
            strengthMeter.style.width = strength + '%';
            
            if (strength < 50) {
                strengthMeter.style.backgroundColor = '#e74c3c';
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#f39c12';
            } else {
                strengthMeter.style.backgroundColor = '#27ae60';
            }
        });
        
        // Валидация ФИО
        const fullNameInput = document.getElementById('full_name');
        
        fullNameInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Zа-яА-ЯёЁ\s\-\.']/gu, '');
        });

        document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById('phone');
            
            phoneInput.addEventListener('input', function(e) {
                let number = e.target.value.replace(/\D/g, '');
                
                
                if(number.startsWith('8') && number.length > 1) {
                    number = '7' + number.substring(1);
                }
                
               
                let formatted = '+7';
                if(number.length > 1) {
                    formatted += ' (' + number.substring(1, 4);
                }
                if(number.length >= 5) {
                    formatted += ') ' + number.substring(4, 7);
                }
                if(number.length >= 8) {
                    formatted += '-' + number.substring(7, 9);
                }
                if(number.length >= 10) {
                    formatted += '-' + number.substring(9, 11);
                }
                
                
                e.target.value = formatted;
            });

           
            phoneInput.addEventListener('change', function(e) {
                e.target.value = e.target.value.replace(/[^\d+()-\s]/g, '');
            });
        });
    </script>
</body>
</html>