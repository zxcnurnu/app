<?php
// admin.php
include 'config.php';
include 'functions.php';
check_admin();

$orders = get_all_orders($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    // Если статус изменен на "cancelled" или "completed", возвращаем инвентарь
    if ($new_status === 'cancelled' || $new_status === 'completed') {
        $stmt = $pdo->prepare("
            UPDATE equipment e
            JOIN orders o ON e.equipment_id = o.equipment_id
            SET e.available_quantity = e.available_quantity + 1
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
    }
    
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора | СпортGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #4CAF50;
            --accent: #FF9800;
            --dark: #2c3e50;
            --light: #f5f5f5;
            --danger: #e74c3c;
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
            max-width: 1400px;
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
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: white;
            border-radius: 12px 12px 0 0;
            margin-bottom: 30px;
        }
        
        .header-title {
            font-size: 28px;
            font-weight: 600;
        }
        
        .logout-btn {
            padding: 12px 25px;
            background: var(--danger);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
        }
        
        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.4);
            background: #c0392b;
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
            position: sticky;
            top: 0;
        }
        
        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            background: white;
        }
        
        .orders-table tr:hover td {
            background-color: #f9f9f9;
        }
        
        .status-form {
            display: flex;
            gap: 10px;
        }
        
        .status-select {
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            min-width: 120px;
        }
        
        .update-btn {
            padding: 8px 15px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .update-btn:hover {
            background: #3d8b40;
            transform: translateY(-2px);
        }
        
        .text-des {
            padding: 8px 15px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
            min-width: 100px;
            text-align: center;
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
        
        .period-cell {
            white-space: nowrap;
        }
        
        /* Мобильная адаптация */
        @media (max-width: 1200px) {
            .container {
                max-width: 95%;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 992px) {
            .orders-table th, 
            .orders-table td {
                padding: 12px 10px;
                font-size: 14px;
            }
            
            .status-form {
                flex-direction: column;
                gap: 8px;
            }
            
            .status-select {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .sports-bg {
                padding: 20px 15px;
            }
            
            .header-title {
                font-size: 24px;
            }
            
            .logout-btn {
                width: 100%;
                justify-content: center;
            }
            
            .orders-table th, 
            .orders-table td {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            .status-badge {
                font-size: 12px;
                padding: 4px 8px;
                min-width: 80px;
            }
        }
        
        @media (max-width: 576px) {
            .period-cell {
                white-space: normal;
            }
            
            .orders-table td:nth-child(1),
            .orders-table td:nth-child(2),
            .orders-table td:nth-child(3),
            .orders-table td:nth-child(4),
            .orders-table td:nth-child(5),
            .orders-table td:nth-child(6),
            .orders-table td:nth-child(7),
            .orders-table td:nth-child(8),
            .orders-table td:nth-child(9),
            .orders-table td:nth-child(10) {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
            
            .orders-table tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
            }
            
            .orders-table td::before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sports-bg">
            <header>
                <h1 class="header-title">Панель администратора</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Выйти
                </a>
            </header>
            
            <h2 class="page-title">Все заказы</h2>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Клиент</th>
                        <th><i class="fas fa-phone"></i> Телефон</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-basketball"></i> Инвентарь</th>
                        <th><i class="fas fa-calendar-alt"></i> Период аренды</th>
                        <th><i class="fas fa-map-marker-alt"></i> Пункт выдачи</th>
                        <th><i class="fas fa-money-bill-wave"></i> Стоимость</th>
                        <th><i class="fas fa-info-circle"></i> Статус</th>
                        <th><i class="fa-solid fa-check"></i> Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="ID"><?= $order['order_id'] ?></td>
                            <td data-label="Клиент"><?= htmlspecialchars($order['full_name']) ?></td>
                            <td data-label="Телефон"><?= htmlspecialchars($order['phone']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($order['email']) ?></td>
                            <td data-label="Инвентарь"><?= htmlspecialchars($order['equipment_name']) ?></td>
                            <td data-label="Период аренды" class="period-cell">
                                <?= date('d.m.Y H:i', strtotime($order['start_time'])) ?> -<br>
                                <?= date('d.m.Y H:i', strtotime($order['end_time'])) ?>
                            </td>
                            <td data-label="Пункт выдачи"><?= htmlspecialchars($order['address']) ?></td>
                            <td data-label="Стоимость"><?= number_format($order['total_price'], 2) ?>₽</td>
                            <td data-label="Статус">
                                <span class="status-badge status-<?= $order['status'] ?>">
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
                            <td data-label="Действия">
                                <form class="status-form" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="new_status" class="status-select" id="select-id">
                                        <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>>Новый</option>
                                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Подтвержден</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Выполнен</option>
                                        <option id="select-id" value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                                    </select>
                                    <textarea name="cancellation_reason" id="text-des" style="display: none" class="text-des" placeholder="Причина отмены услуга"></textarea>
                                    <button type="submit" name="update_status" class="update-btn" onclick="saveToLocalStorage()">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
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
<script>
    const select = document.getElementById("select-id");
    const textarea = document.getElementById("text-des");

    select.addEventListener("change", function() {
        const selectedIndex = select.selectedIndex;
        console.log("Choose option: " + selectedIndex);

        if (selectedIndex === 3) {
            textarea.style.display = textarea.style.display === 'none' ? 'block' : 'none';
        }
    });

    function saveToLocalStorage() {
        const textarea = document.getElementById('text-des');
        const text = textarea.value;
        localStorage.setItem('myText', text);
        alert('Текст сохранен в LocalStorage!');
    }
</script>
</html>