<?php
// dashboard.php
include 'config.php';
include 'functions.php';
check_auth();

$orders = get_user_orders($_SESSION['user_id'], $pdo);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | СпортGo</title>
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
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            padding: 20px;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid var(--accent);
        }
        
        .sports-bg {
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100"><circle cx="50" cy="50" r="10" fill="%233498db" opacity="0.2"/></svg>');
            background-size: 200px;
            padding: 30px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 12px 12px 0 0;
            margin-bottom: 30px;
        }
        
        .welcome {
            font-size: 24px;
            font-weight: 600;
        }
        
        .user-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 20px;
            background: white;
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        
        .logout-btn {
            background: var(--accent);
            color: white;
        }
        
        .logout-btn:hover {
            background: #e68a00;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent);
            color: var(--dark);
            position: relative;
        }
        
        .page-title::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .orders-table th {
            background: linear-gradient(135deg, var(--primary), #2980b9);
            color: white;
            text-align: left;
            font-weight: 600;
            padding: 15px;
        }
        
        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            background: white;
        }
        
        .orders-table tr:hover td {
            background-color: #f9f9f9;
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
        }
        
        .status-new {
            background-color: #e3f2fd;
            color: #3498db;
        }
        
        .status-confirmed {
            background-color: #e8f5e9;
            color: #27ae60;
        }
        
        .status-completed {
            background-color: #f5f5f5;
            color: #7f8c8d;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #e74c3c;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin: 20px 0;
        }
        
        .no-orders p {
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .create-order-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        
        .create-order-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
        
        .sports-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            opacity: 0.7;
        }
        
        .sports-icons i {
            font-size: 24px;
            color: var(--primary);
        }
        
        /* Мобильная адаптация */
        @media (max-width: 992px) {
            .container {
                max-width: 95%;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .user-actions {
                width: 100%;
                justify-content: center;
            }
            
            .orders-table th, 
            .orders-table td {
                padding: 12px 10px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 576px) {
            .sports-bg {
                padding: 20px 15px;
            }
            
            .welcome {
                font-size: 20px;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .orders-table th, 
            .orders-table td {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            .status {
                font-size: 12px;
                padding: 4px 8px;
            }
            
            .no-orders {
                padding: 30px 15px;
            }
            
            .no-orders p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sports-bg">
            <header>
                <h1 class="welcome">Добро пожаловать, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
                <div class="user-actions">
                    <a href="create_order.php" class="btn">
                        <i class="fas fa-plus-circle"></i> Создать заказ
                    </a>
                    <a href="logout.php" class="btn logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </a>
                </div>
            </header>
            
            <h2 class="page-title">История заказов</h2>
            
            <?php if (count($orders) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-basketball"></i> Инвентарь</th>
                            <th><i class="fas fa-calendar-start"></i> Дата начала</th>
                            <th><i class="fas fa-calendar-check"></i> Дата окончания</th>
                            <th><i class="fas fa-map-marker-alt"></i> Пункт выдачи</th>
                            <th><i class="fas fa-money-bill-wave"></i> Стоимость</th>
                            <th><i class="fas fa-info-circle"></i> Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['equipment_name']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['start_time'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['end_time'])) ?></td>
                                <td><?= htmlspecialchars($order['address']) ?></td>
                                <td><?= number_format($order['total_price'], 2) ?>₽</td>
                                <td>
                                    <span class="status status-<?= $order['status'] ?>">
                                        <?php 
                                        $statuses = [
                                            'new' => 'Новый',
                                            'confirmed' => 'Подтвержден',
                                            'completed' => 'Выполнен',
                                            'cancelled' => 'Отменен'
                                        ];
                                        echo $statuses[$order['status']];
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-orders">
                    <p><i class="fas fa-shopping-cart fa-2x"></i></p>
                    <p>У вас пока нет заказов</p>
                    <a href="create_order.php" class="create-order-btn">
                        <i class="fas fa-plus"></i> Оформить первый заказ
                    </a>
                </div>
            <?php endif; ?>
            
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