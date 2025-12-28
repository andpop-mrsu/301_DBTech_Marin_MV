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

$sql = "SELECT a.id, a.service_id, s.name AS service_name, a.car_category_id, cc.name AS category_name, a.client_name, a.appointment_date, a.appointment_time, a.status FROM Appointment a JOIN Service s ON a.service_id = s.id JOIN CarCategory cc ON a.car_category_id = cc.id WHERE a.employee_id = ? ORDER BY a.appointment_date, a.appointment_time";
$stmt = $pdo->prepare($sql);
$stmt->execute([$employee_id]);
$appointments = $stmt->fetchAll();

function formatFullName($first_name, $last_name) {
    return htmlspecialchars($last_name . ' ' . $first_name, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Записи - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></title>
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
        .status-scheduled { color: #007bff; }
        .status-completed { color: #28a745; }
        .status-cancelled { color: #dc3545; }
        .status-no_show { color: #ffc107; }
    </style>
</head>
<body>

    <a class="btn-back" href="index.php">Назад к списку сотрудников</a>
    <h1>Предстоящие и прошедшие записи - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></h1>

    <table>
        <thead>
            <tr>
                <th>Услуга</th>
                <th>Категория авто</th>
                <th>Клиент</th>
                <th>Дата</th>
                <th>Время</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($appointments) > 0): ?>
                <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?= htmlspecialchars($appt['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($appt['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($appt['client_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($appt['appointment_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($appt['appointment_time'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="<?= 'status-' . $appt['status'] ?>"><?= htmlspecialchars(ucfirst($appt['status']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="actions">
                            <a class="btn" href="edit_appointment.php?id=<?= $appt['id'] ?>">Редактировать</a>
                            <a class="btn" href="delete_appointment.php?id=<?= $appt['id'] ?>">Удалить</a>
                            <a class="btn" href="view_work_record.php?appointment_id=<?= $appt['id'] ?>">Работа</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Для этого сотрудника пока нет записей.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a class="btn-add" href="add_appointment.php?employee_id=<?= $employee_id ?>">Добавить Запись</a>

</body>
</html>