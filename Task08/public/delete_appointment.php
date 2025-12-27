<?php

include_once __DIR__ . '/../config/config.php';

$appointment_id = $_GET['id'] ?? null;
$message = '';
$messageType = '';
$appointment = null;

if (!$appointment_id || !is_numeric($appointment_id)) {
    die('Неверный ID записи.');
}

$sql = "SELECT a.id, a.employee_id, a.service_id, a.car_category_id, a.client_name, a.appointment_date, a.appointment_time, a.status, a.scheduled_price, e.first_name, e.last_name, s.name AS service_name, cc.name AS category_name FROM Appointment a JOIN Employee e ON a.employee_id = e.id JOIN Service s ON a.service_id = s.id JOIN CarCategory cc ON a.car_category_id = cc.id WHERE a.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$appointment_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die('Запись не найдена.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $sql = "DELETE FROM Appointment WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$appointment_id]);

        $message = 'Запись успешно удалена.';
        $messageType = 'success';
        $appointment = null;
    } catch (PDOException $e) {
        $message = 'Ошибка при удалении записи: ' . $e->getMessage();
        $messageType = 'error';
    }
}

function formatFullName($first_name, $last_name) {
    return htmlspecialchars($last_name . ' ' . $first_name, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить Запись - <?= formatFullName($appointment['first_name'], $appointment['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .btn { padding: 10px 15px; margin-right: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background-color: #45a049; }
        .btn-cancel { background-color: #f44336; }
        .btn-cancel:hover { background-color: #da190b; }
        .btn-confirm { background-color: #f44336; }
        .btn-confirm:hover { background-color: #da190b; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0; }
        .info { padding: 10px; background-color: #e7f3fe; border: 1px solid #b3d9ff; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>

    <h1>Удалить Запись</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php if ($messageType === 'success'): ?>
            <a href="employee_schedule.php?employee_id=<?= $appointment['employee_id'] ?>" class="btn">Вернуться к записям</a>
        <?php else: ?>
            <a href="employee_schedule.php?employee_id=<?= $appointment['employee_id'] ?>" class="btn btn-cancel">Назад</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="info">
            <p><strong>ID записи:</strong> <?= $appointment['id'] ?></p>
        </div>

        <div class="warning">
            <p>Вы уверены, что хотите удалить запись?</p>
            <p><strong>Сотрудник:</strong> <?= formatFullName($appointment['first_name'], $appointment['last_name']) ?></p>
            <p><strong>Услуга:</strong> <?= htmlspecialchars($appointment['service_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Категория авто:</strong> <?= htmlspecialchars($appointment['category_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Клиент:</strong> <?= htmlspecialchars($appointment['client_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Дата/Время:</strong> <?= htmlspecialchars($appointment['appointment_date'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($appointment['appointment_time'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Статус:</strong> <?= htmlspecialchars($appointment['status'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Предполагаемая цена:</strong> <?= number_format($appointment['scheduled_price'], 2, '.', ' ') ?> руб.</p>
            <p><strong>Внимание!</strong> Это действие также удалит связанную запись о выполненной работе (WorkRecord), если она существует (из-за ON DELETE CASCADE в БД).</p>
        </div>

        <form method="post">
            <input type="hidden" name="confirm_delete" value="1">
            <button type="submit" class="btn btn-confirm">Да, удалить</button>
            <a href="employee_schedule.php?employee_id=<?= $appointment['employee_id'] ?>" class="btn btn-cancel">Нет, отмена</a>
        </form>
    <?php endif; ?>

</body>
</html>