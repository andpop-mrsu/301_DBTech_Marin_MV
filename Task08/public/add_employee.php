<?php

include_once __DIR__ . '/../config/config.php';

$message = '';
$messageType = '';

$first_name = '';
$last_name = '';
$phone = '';
$email = '';
$hire_date = date('Y-m-d');
$salary_percentage = 25.00;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hire_date = $_POST['hire_date'] ?? null;
    $salary_percentage = filter_var($_POST['salary_percentage'] ?? 25.00, FILTER_VALIDATE_FLOAT);

    if ($first_name && $last_name && $hire_date && $salary_percentage !== false && $salary_percentage >= 0 && $salary_percentage <= 100) {
        try {
            $sql = "INSERT INTO Employee (first_name, last_name, phone, email, hire_date, salary_percentage) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $phone, $email, $hire_date, $salary_percentage]);

            $message = 'Сотрудник успешно добавлен.';
            $messageType = 'success';
            $first_name = $last_name = $phone = $email = $hire_date = '';
            $salary_percentage = 25.00;
        } catch (PDOException $e) {
            $message = 'Ошибка при добавлении сотрудника: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Пожалуйста, заполните обязательные поля корректно (Фамилия, Имя, Дата найма, Процент от выручки 0-100).';
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить Сотрудника</title>
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
    </style>
</head>
<body>

    <h1>Добавить Нового Сотрудника</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="first_name">Имя *:</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Фамилия *:</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label for="hire_date">Дата найма *:</label>
            <input type="date" id="hire_date" name="hire_date" value="<?= $hire_date ?>" required>
        </div>
        <div class="form-group">
            <label for="salary_percentage">Процент от выручки (%):</label>
            <input type="number" id="salary_percentage" name="salary_percentage" step="0.01" min="0" max="100" value="<?= $salary_percentage ?>" required placeholder="25.00">
        </div>
        <button type="submit" class="btn">Сохранить</button>
        <a href="index.php" class="btn btn-cancel">Отмена</a>
    </form>

</body>
</html>