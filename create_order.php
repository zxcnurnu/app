<?php
// create_order.php
include 'config.php';
include 'functions.php';
check_auth();

$equipment = $pdo->query("SELECT * FROM equipment WHERE available_quantity > 0")->fetchAll();
$points = $pdo->query("SELECT * FROM pickup_points")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = (int)$_POST['equipment_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $point_id = (int)$_POST['point_id'];
    $payment_method = $_POST['payment_method'];
    
    // Расчет часов
    $hours = (strtotime($end_time) - strtotime($start_time)) / 3600;
    
    // Получение цены
    $stmt = $pdo->prepare("SELECT price_per_hour FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    $price = $stmt->fetchColumn();
    $total_price = $hours * $price;
    
    // Создание заказа
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (user_id, equipment_id, point_id, start_time, end_time, total_price, payment_method) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $equipment_id,
        $point_id,
        $start_time,
        $end_time,
        $total_price,
        $payment_method
    ]);
    
    // Обновление доступного количества
    $stmt = $pdo->prepare("UPDATE equipment SET available_quantity = available_quantity - 1 WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа | СпортGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #4CAF50;
            --accent: #FF9800;
            --dark: #2c3e50;
            --light: #f5f5f5;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #1a2a6c, #b21f1f);
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
            max-width: 700px;
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
            margin-bottom: 25px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            font-size: 16px;
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
            z-index: 1;
        }
        
        select, input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: white;
            appearance: none;
        }
        
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233498db'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }
        
        select:focus, input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        .radio-group {
            display: flex;
            gap: 25px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .radio-group label {
            display: flex;
            align-items: center;
            font-weight: normal;
            padding: 12px 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 2px solid #eee;
            transition: all 0.3s;
            cursor: pointer;
            margin-bottom: 0;
        }
        
        .radio-group label:hover {
            background: #e3f2fd;
            border-color: var(--primary);
        }
        
        .radio-group input {
            width: auto;
            margin-right: 10px;
        }
        
        button {
            width: 100%;
            padding: 16px;
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
            margin-top: 10px;
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            padding: 10px;
        }
        
        .back-link:hover {
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
        
        .price-preview {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-weight: 600;
            color: var(--dark);
            display: none;
        }
        
        /* Мобильная адаптация */
        @media (max-width: 768px) {
            .container {
                max-width: 95%;
            }
            
            .sports-bg {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .radio-group {
                gap: 15px;
            }
            
            .radio-group label {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .sports-bg {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            input, select {
                padding: 12px 12px 12px 40px;
                font-size: 15px;
            }
            
            button {
                padding: 14px;
                font-size: 16px;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sports-bg">
            <div class="logo">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            
            <div class="header">
                <h1>Оформление заказа</h1>
                <p>Выбери инвентарь и оформи аренду</p>
            </div>

            <form method="POST" id="orderForm">
                <div class="form-group">
                    <label for="equipment_id">Спортивный инвентарь:</label>
                    <div class="input-icon">
                        <i class="fas fa-basketball"></i>
                        <select name="equipment_id" id="equipment_id" required>
                            <?php foreach($equipment as $item): ?>
                                <option value="<?= $item['equipment_id'] ?>"
                                        data-price="<?= $item['price_per_hour'] ?>">
                                    <?= htmlspecialchars($item['name']) ?> 
                                    (<?= $item['price_per_hour'] ?>₽/час)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="start_time">Дата и время начала аренды:</label>
                    <div class="input-icon">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="datetime-local" name="start_time" id="start_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="end_time">Дата и время окончания аренды:</label>
                    <div class="input-icon">
                        <i class="fas fa-calendar-check"></i>
                        <input type="datetime-local" name="end_time" id="end_time" required>
                    </div>
                    <div class="price-preview" id="pricePreview">
                        Стоимость аренды: <span id="priceValue">0</span>₽
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="point_id">Пункт выдачи:</label>
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <select name="point_id" id="point_id" required>
                            <?php foreach($points as $point): ?>
                                <option value="<?= $point['point_id'] ?>">
                                    <?= htmlspecialchars($point['address']) ?> 
                                    (<?= htmlspecialchars($point['working_hours']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Способ оплаты:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="payment_method" value="cash" checked>
                            <i class="fas fa-money-bill-wave"></i> Наличные
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="card">
                            <i class="fas fa-credit-card"></i> Карта
                        </label>
                    </div>
                </div>
                
                <button type="submit">
                    <i class="fas fa-check-circle"></i> Подтвердить заказ
                </button>
            </form>
            
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Вернуться в личный кабинет
            </a>
            
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
        // Расчет стоимости в реальном времени
        const equipmentSelect = document.getElementById('equipment_id');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const pricePreview = document.getElementById('pricePreview');
        const priceValue = document.getElementById('priceValue');
        
        function calculatePrice() {
            const startTime = new Date(startTimeInput.value);
            const endTime = new Date(endTimeInput.value);
            
            if (startTime && endTime && startTime < endTime) {
                const hours = (endTime - startTime) / (1000 * 60 * 60);
                const selectedOption = equipmentSelect.options[equipmentSelect.selectedIndex];
                const pricePerHour = parseFloat(selectedOption.dataset.price);
                const totalPrice = (hours * pricePerHour).toFixed(2);
                
                priceValue.textContent = totalPrice;
                pricePreview.style.display = 'block';
            } else {
                pricePreview.style.display = 'none';
            }
        }
        
        equipmentSelect.addEventListener('change', calculatePrice);
        startTimeInput.addEventListener('change', calculatePrice);
        endTimeInput.addEventListener('change', calculatePrice);
        
        // Установка минимальной даты (текущая дата)
        const now = new Date();
        const minDate = now.toISOString().slice(0, 16);
        startTimeInput.min = minDate;
        endTimeInput.min = minDate;
    </script>
</body>
</html>