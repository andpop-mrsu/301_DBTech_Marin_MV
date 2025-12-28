<?php

include_once __DIR__ . '/../config/config.php';

$employee_id = $_GET['id'] ?? null;
$message = '';
$messageType = '';
$employee = null;

if (!$employee_id || !is_numeric($employee_id)) {
    die('Неверный ID сотрудника.');
}

$sql = "SELECT id, first_name, last_name, phone, email, hire_date, dismissal_date, salary_percentage, is_active FROM Employee WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$employee_id]);
$employee = $stmt->fetch();

if (!$employee) {
    die('Сотрудник не найден.');
}

if ($employee['is_active'] == 0) {
    $message = 'Сотрудник уже уволен.';
    $messageType = 'info';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_firing']) && $employee['is_active'] == 1) {
    $dismissal_date = $_POST['dismissal_date'] ?? date('Y-m-d');

    // Проверка: если дата увольнения не пустая, она должна быть больше даты найма
    if ($dismissal_date && strtotime($dismissal_date) <= strtotime($employee['hire_date'])) {
        $message = 'Дата увольнения должна быть позже даты найма.';
        $messageType = 'error';
    } else {
        try {
            $sql = "UPDATE Employee SET dismissal_date = ?, is_active = 0 WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dismissal_date, $employee_id]);

            $message = 'Сотрудник успешно уволен.';
            $messageType = 'success';
            $employee['dismissal_date'] = $dismissal_date;
            $employee['is_active'] = 0;
        } catch (PDOException $e) {
            $message = 'Ошибка при увольнении сотрудника: ' . $e->getMessage();
            $messageType = 'error';
        }
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
    <title>Уволить Сотрудника - <?= formatFullName($employee['first_name'], $employee['last_name']) ?></title>
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
        .message.info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0; }
        .info { padding: 10px; background-color: #e7f3fe; border: 1px solid #b3d9ff; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>

    <h1>Уволить Сотрудника</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php if ($messageType === 'success' || $messageType === 'info'): ?>
            <a href="index.php" class="btn">Вернуться к списку</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="info">
            <p><strong>ФИО:</strong> <?= formatFullName($employee['first_name'], $employee['last_name']) ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($employee['phone'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($employee['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Дата найма:</strong> <?= htmlspecialchars($employee['hire_date'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="warning">
            <p>Вы уверены, что хотите уволить этого сотрудника?</p>
            <p>Это действие установит дату увольнения и снимет статус "активен". Данные о сотруднике и его работе (записи, работы) останутся в базе.</p>
        </div>

        <form method="post">
            <div class="form-group">
                <label for="dismissal_date">Дата увольнения:</label>
                <input type="date" id="dismissal_date" name="dismissal_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <input type="hidden" name="confirm_firing" value="1">
            <button type="submit" class="btn btn-confirm">Да, уволить</button>
            <a href="index.php" class="btn btn-cancel">Нет, отмена</a>
        </form>
    <?php endif; ?>

</body>
</html>