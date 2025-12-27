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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $salary_percentage = filter_var($_POST['salary_percentage'] ?? 25.00, FILTER_VALIDATE_FLOAT);

    if ($first_name && $last_name && $salary_percentage !== false && $salary_percentage >= 0 && $salary_percentage <= 100) {
        try {
            $sql = "UPDATE Employee SET first_name = ?, last_name = ?, phone = ?, email = ?, salary_percentage = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $phone, $email, $salary_percentage, $employee_id]);

            $message = 'Данные сотрудника успешно обновлены.';
            $messageType = 'success';
            $employee['first_name'] = $first_name;
            $employee['last_name'] = $last_name;
            $employee['phone'] = $phone;
            $employee['email'] = $email;
            $employee['salary_percentage'] = $salary_percentage;
        } catch (PDOException $e) {
            $message = 'Ошибка при обновлении данных сотрудника: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Пожалуйста, заполните обязательные поля корректно (Фамилия, Имя, Процент от выручки 0-100).';
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
    <title>Редактировать Сотрудника - <?= formatFullName($employee['first_name'], $employee['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="tel"], input[type="email"], input[type="date"], input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; }
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

    <h1>Редактировать Сотрудника: <?= formatFullName($employee['first_name'], $employee['last_name']) ?></h1>

    <div class="info">
        <p><strong>ID:</strong> <?= $employee['id'] ?></p>
        <p><strong>Дата найма:</strong> <?= htmlspecialchars($employee['hire_date'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Дата увольнения:</strong> <?= $employee['dismissal_date'] ? htmlspecialchars($employee['dismissal_date'], ENT_QUOTES, 'UTF-8') : 'Не уволен' ?></p>
        <p><strong>Статус:</strong> <?= $employee['is_active'] ? 'Активен' : 'Уволен' ?></p>
    </div>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="first_name">Имя *:</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($employee['first_name'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Фамилия *:</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($employee['last_name'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($employee['phone'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($employee['email'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label for="salary_percentage">Процент от выручки (%):</label>
            <input type="number" id="salary_percentage" name="salary_percentage" step="0.01" min="0" max="100" value="<?= $employee['salary_percentage'] ?>" required placeholder="25.00">
        </div>
        <button type="submit" class="btn">Сохранить изменения</button>
        <a href="index.php" class="btn btn-cancel">Отмена</a>
    </form>

</body>
</html>