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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_date = $_POST['work_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($work_date && $start_time && $end_time && strtotime($end_time) > strtotime($start_time)) {
        try {
            $start_datetime = new DateTime("$work_date $start_time");
            $end_datetime = new DateTime("$work_date $end_time");
            $duration_seconds = $end_datetime->getTimestamp() - $start_datetime->getTimestamp();
            $actual_duration = $duration_seconds / 60;

            $sql = "UPDATE WorkRecord SET actual_duration = ?, work_date = ?, start_time = ?, end_time = ?, notes = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$actual_duration, $work_date, $start_time, $end_time, $notes, $work_id]);

            $message = 'Данные выполненной работы успешно обновлены.';
            $messageType = 'success';
            $work_record['actual_duration'] = $actual_duration;
            $work_record['work_date'] = $work_date;
            $work_record['start_time'] = $start_time;
            $work_record['end_time'] = $end_time;
            $work_record['notes'] = $notes;
        } catch (PDOException $e) {
            $message = 'Ошибка при обновлении выполненной работы: ' . $e->getMessage();
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
    <title>Редактировать Выполненную Работу - <?= formatFullName($work_record['first_name'], $work_record['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="date"], input[type="time"], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 10px 15px; margin-right: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background-color: #45a049; }
        .btn-cancel { background-color: #f44336; }
        .btn-cancel:hover { background-color: #da190b; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { padding: 10px; background-color: #e7f3fe; border: 1px solid #b3d9ff; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>

    <h1>Редактировать Выполненную Работу - <?= formatFullName($work_record['first_name'], $work_record['last_name']) ?></h1>

    <div class="info">
        <p><strong>ID работы:</strong> <?= $work_record['id'] ?></p>
        <p><strong>Связанная запись (ID):</strong> <?= $work_record['appointment_id'] ?></p>
        <p><strong>Услуга:</strong> <?= htmlspecialchars($work_record['service_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Категория авто:</strong> <?= htmlspecialchars($work_record['category_name'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="work_date">Дата выполнения *:</label>
            <input type="date" id="work_date" name="work_date" value="<?= htmlspecialchars($work_record['work_date'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="start_time">Время начала *:</label>
            <input type="time" id="start_time" name="start_time" value="<?= htmlspecialchars($work_record['start_time'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="end_time">Время окончания *:</label>
            <input type="time" id="end_time" name="end_time" value="<?= htmlspecialchars($work_record['end_time'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="notes">Примечания:</label>
            <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($work_record['notes'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <button type="submit" class="btn">Сохранить изменения</button>
        <a href="employee_works.php?employee_id=<?= $work_record['employee_id'] ?>" class="btn btn-cancel">Отмена</a>
    </form>

</body>
</html>