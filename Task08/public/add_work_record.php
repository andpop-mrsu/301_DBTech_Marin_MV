<?php

include_once __DIR__ . '/../config/config.php';

$employee_id = $_GET['employee_id'] ?? null;
$message = '';
$messageType = '';

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

$sql_appts = "
    SELECT a.id, s.name AS service_name, cc.name AS category_name, a.client_name, a.appointment_date, a.appointment_time, a.scheduled_duration, a.scheduled_price
    FROM Appointment a
    JOIN Service s ON a.service_id = s.id
    JOIN CarCategory cc ON a.car_category_id = cc.id
    WHERE a.employee_id = ? AND a.status = 'scheduled'
    ORDER BY a.appointment_date, a.appointment_time
";
$stmt_appts = $pdo->prepare($sql_appts);
$stmt_appts->execute([$employee_id]);
$appointments = $stmt_appts->fetchAll();

// Инициализация переменных
$appointment_id = null;
$work_date = date('Y-m-d');
$start_time = '';
$end_time = '';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'] ?? null;
    $work_date = $_POST['work_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($appointment_id && $work_date && $start_time && $end_time && strtotime($end_time) > strtotime($start_time)) {
        try {
            $sql_app_data = "SELECT service_id, car_category_id, employee_id, scheduled_price, scheduled_duration FROM Appointment WHERE id = ?";
            $stmt_app_data = $pdo->prepare($sql_app_data);
            $stmt_app_data->execute([$appointment_id]);
            $app_data = $stmt_app_data->fetch();

            if (!$app_data) {
                throw new Exception("Связанная запись не найдена.");
            }

            $start_datetime = new DateTime("$work_date $start_time");
            $end_datetime = new DateTime("$work_date $end_time");
            $duration_seconds = $end_datetime->getTimestamp() - $start_datetime->getTimestamp();
            $actual_duration = $duration_seconds / 60;

            $actual_price = $app_data['scheduled_price'];

            $sql = "INSERT INTO WorkRecord (appointment_id, employee_id, service_id, car_category_id, actual_duration, actual_price, work_date, start_time, end_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $appointment_id, $app_data['employee_id'], $app_data['service_id'], $app_data['car_category_id'],
                $actual_duration, $actual_price, $work_date, $start_time, $end_time, $notes
            ]);

            $sql_update_app = "UPDATE Appointment SET status = 'completed' WHERE id = ?";
            $stmt_update_app = $pdo->prepare($sql_update_app);
            $stmt_update_app->execute([$appointment_id]);

            $message = 'Выполненная работа успешно добавлена и запись обновлена.';
            $messageType = 'success';
            $appointment_id = null;
            $work_date = date('Y-m-d');
            $start_time = $end_time = '';
            $notes = '';
        } catch (Exception $e) {
            $message = 'Ошибка при добавлении выполненной работы: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Пожалуйста, заполните все поля корректно (дата, время начала < время окончания).';
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
    <title>Добавить Выполненную Работу - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select, input[type="date"], input[type="time"], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 10px 15px; margin-right: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background-color: #45a049; }
        .btn-cancel { background-color: #f44336; }
        .btn-cancel:hover { background-color: #da190b; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <h1>Добавить Выполненную Работу - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
        <div class="form-group">
            <label for="appointment_id">Связанная запись (Appointment) *:</label>
            <select id="appointment_id" name="appointment_id" required>
                <option value="">Выберите запись...</option>
                <?php foreach ($appointments as $appt): ?>
                    <option value="<?= $appt['id'] ?>" <?= ($appt['id'] == $appointment_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($appt['appointment_date'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($appt['appointment_time'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($appt['service_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($appt['client_name'], ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="work_date">Дата выполнения *:</label>
            <input type="date" id="work_date" name="work_date" value="<?= $work_date ?>" required>
        </div>
        <div class="form-group">
            <label for="start_time">Время начала *:</label>
            <input type="time" id="start_time" name="start_time" value="<?= $start_time ?>" required>
        </div>
        <div class="form-group">
            <label for="end_time">Время окончания *:</label>
            <input type="time" id="end_time" name="end_time" value="<?= $end_time ?>" required>
        </div>
        <div class="form-group">
            <label for="notes">Примечания:</label>
            <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <button type="submit" class="btn">Сохранить</button>
        <a href="employee_works.php?employee_id=<?= $employee_id ?>" class="btn btn-cancel">Отмена</a>
    </form>

</body>
</html>