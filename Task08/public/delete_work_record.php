<?php

include_once __DIR__ . '/../config/config.php';

$work_id = $_GET['id'] ?? null;
$message = '';
$messageType = '';
$work_record = null;

if (!$work_id || !is_numeric($work_id)) {
    die('Неверный ID выполненной работы.');
}

$sql = "SELECT wr.id, wr.appointment_id, wr.employee_id, wr.service_id, wr.car_category_id, wr.actual_duration, wr.actual_price, wr.work_date, wr.start_time, wr.end_time, wr.notes, e.first_name, e.last_name, s.name AS service_name, cc.name AS category_name FROM WorkRecord wr JOIN Employee e ON wr.employee_id = e.id JOIN Service s ON wr.service_id = s.id JOIN CarCategory cc ON wr.car_category_id = cc.id WHERE wr.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$work_id]);
$work_record = $stmt->fetch();

if (!$work_record) {
    die('Выполненная работа не найдена.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $sql = "DELETE FROM WorkRecord WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$work_id]);

        $message = 'Выполненная работа успешно удалена.';
        $messageType = 'success';
        $work_record = null;
    } catch (PDOException $e) {
        $message = 'Ошибка при удалении выполненной работы: ' . $e->getMessage();
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
    <title>Удалить Выполненную Работу - <?= formatFullName($work_record['first_name'], $work_record['last_name']) ?></title>
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

    <h1>Удалить Выполненную Работу</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php if ($messageType === 'success'): ?>
            <a href="employee_works.php?employee_id=<?= $work_record['employee_id'] ?>" class="btn">Вернуться к списку работ</a>
        <?php else: ?>
            <a href="employee_works.php?employee_id=<?= $work_record['employee_id'] ?>" class="btn btn-cancel">Назад</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="info">
            <p><strong>ID работы:</strong> <?= $work_record['id'] ?></p>
        </div>

        <div class="warning">
            <p>Вы уверены, что хотите удалить запись выполненной работы?</p>
            <p><strong>Сотрудник:</strong> <?= formatFullName($work_record['first_name'], $work_record['last_name']) ?></p>
            <p><strong>Услуга:</strong> <?= htmlspecialchars($work_record['service_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Категория авто:</strong> <?= htmlspecialchars($work_record['category_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Дата:</strong> <?= htmlspecialchars($work_record['work_date'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Время:</strong> <?= htmlspecialchars($work_record['start_time'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($work_record['end_time'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Факт. длительность:</strong> <?= $work_record['actual_duration'] ?> мин.</p>
            <p><strong>Факт. цена:</strong> <?= number_format($work_record['actual_price'], 2, '.', ' ') ?> руб.</p>
            <p><strong>Примечания:</strong> <?= htmlspecialchars($work_record['notes'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <form method="post">
            <input type="hidden" name="confirm_delete" value="1">
            <button type="submit" class="btn btn-confirm">Да, удалить</button>
            <a href="employee_works.php?employee_id=<?= $work_record['employee_id'] ?>" class="btn btn-cancel">Нет, отмена</a>
        </form>
    <?php endif; ?>

</body>
</html>