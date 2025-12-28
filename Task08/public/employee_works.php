<?php

include_once __DIR__ . '/../config/config.php';

$employee_id = $_GET['employee_id'] ?? null;

if (!$employee_id || !is_numeric($employee_id)) {
    die('Неверный ID сотрудника.');
}

$sql = "SELECT first_name, last_name FROM Employee WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$employee_id]);
$employee_info = $stmt->fetch();

if (!$employee_info) {
    die('Сотрудник не найден.');
}

$sql = "SELECT wr.id, wr.appointment_id, s.name AS service_name, cc.name AS category_name, wr.actual_duration, wr.actual_price, wr.work_date, wr.start_time, wr.end_time FROM WorkRecord wr JOIN Service s ON wr.service_id = s.id JOIN CarCategory cc ON wr.car_category_id = cc.id WHERE wr.employee_id = ? ORDER BY wr.work_date DESC, wr.start_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$employee_id]);
$works = $stmt->fetchAll();

function formatFullName($first_name, $last_name) {
    return htmlspecialchars($last_name . ' ' . $first_name, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные работы - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions { white-space: nowrap; }
        .btn { margin: 0 2px; padding: 4px 8px; text-decoration: none; border: 1px solid #ccc; background-color: #f9f9f9; color: #333; cursor: pointer; }
        .btn:hover { background-color: #e0e0e0; }
        .btn-back { display: inline-block; margin-bottom: 10px; padding: 8px 16px; background-color: #008CBA; color: white; text-decoration: none; }
        .btn-back:hover { background-color: #007B9A; }
        .btn-add { display: inline-block; margin-top: 10px; padding: 8px 16px; background-color: #4CAF50; color: white; text-decoration: none; }
        .btn-add:hover { background-color: #45a049; }
    </style>
</head>
<body>

    <a class="btn-back" href="index.php">Назад к списку сотрудников</a>
    <h1>Выполненные работы - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></h1>

    <table>
        <thead>
            <tr>
                <th>Услуга</th>
                <th>Категория авто</th>
                <th>Дата</th>
                <th>Время начала</th>
                <th>Время окончания</th>
                <th>Факт. длительность (мин)</th>
                <th>Факт. цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($works) > 0): ?>
                <?php foreach ($works as $work): ?>
                    <tr>
                        <td><?= htmlspecialchars($work['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($work['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($work['work_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($work['start_time'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($work['end_time'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $work['actual_duration'] ?></td>
                        <td><?= number_format($work['actual_price'], 2, '.', ' ') ?> руб.</td>
                        <td class="actions">
                            <a class="btn" href="edit_work_record.php?id=<?= $work['id'] ?>">Редактировать</a>
                            <a class="btn" href="delete_work_record.php?id=<?= $work['id'] ?>">Удалить</a>
                            <a class="btn" href="view_appointment.php?id=<?= $work['appointment_id'] ?>">Запись</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Для этого сотрудника пока нет выполненных работ.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a class="btn-add" href="add_work_record.php?employee_id=<?= $employee_id ?>">Добавить Выполненную Работу</a>

</body>
</html>