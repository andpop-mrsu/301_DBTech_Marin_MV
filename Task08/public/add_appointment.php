<?php

include_once __DIR__ . '/../config/config.php';

$employee_id = $_GET['employee_id'] ?? null;
$message = '';
$messageType = '';

if (!$employee_id || !is_numeric($employee_id)) {
    die('Неверный ID сотрудника.');
}

$sql = "SELECT first_name, last_name, is_active FROM Employee WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$employee_id]);
$employee_info = $stmt->fetch();

if (!$employee_info || !$employee_info['is_active']) {
    die('Сотрудник не найден или неактивен.');
}

$sql_services = "SELECT id, name FROM Service ORDER BY name";
$stmt_services = $pdo->prepare($sql_services);
$stmt_services->execute();
$services = $stmt_services->fetchAll();

$sql_categories = "SELECT id, name FROM CarCategory ORDER BY name";
$stmt_categories = $pdo->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll();

$sql_specs = "SELECT service_id FROM EmployeeSpecialization WHERE employee_id = ?";
$stmt_specs = $pdo->prepare($sql_specs);
$stmt_specs->execute([$employee_id]);
$spec_ids = $stmt_specs->fetchAll(PDO::FETCH_COLUMN);

// Инициализация переменных
$service_id = null;
$car_category_id = null;
$client_name = '';
$client_phone = '';
$car_model = '';
$car_license_plate = '';
$appointment_date = date('Y-m-d');
$appointment_time = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? null;
    $car_category_id = $_POST['car_category_id'] ?? null;
    $client_name = trim($_POST['client_name'] ?? '');
    $client_phone = trim($_POST['client_phone'] ?? '');
    $car_model = trim($_POST['car_model'] ?? '');
    $car_license_plate = trim($_POST['car_license_plate'] ?? '');
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';

    if (!in_array($service_id, $spec_ids)) {
        $message = 'Сотрудник не специализируется на выбранной услуге.';
        $messageType = 'error';
    } elseif ($service_id && $car_category_id && $client_name && $car_model && $appointment_date && $appointment_time) {
        try {
            $sql_price = "SELECT actual_price FROM ServicePrice WHERE service_id = ? AND car_category_id = ? ORDER BY effective_date DESC LIMIT 1";
            $stmt_price = $pdo->prepare($sql_price);
            $stmt_price->execute([$service_id, $car_category_id]);
            $price_row = $stmt_price->fetch();

            if (!$price_row) {
                throw new Exception("Цена для выбранной услуги и категории автомобиля не найдена.");
            }

            $scheduled_price = $price_row['actual_price'];
            $sql_service_info = "SELECT duration_minutes FROM Service WHERE id = ?";
            $stmt_service_info = $pdo->prepare($sql_service_info);
            $stmt_service_info->execute([$service_id]);
            $service_info = $stmt_service_info->fetch();
            $scheduled_duration = $service_info['duration_minutes'];

            $sql = "INSERT INTO Appointment (employee_id, service_id, car_category_id, client_name, client_phone, car_model, car_license_plate, appointment_date, appointment_time, scheduled_duration, scheduled_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employee_id, $service_id, $car_category_id, $client_name, $client_phone, $car_model, $car_license_plate, $appointment_date, $appointment_time, $scheduled_duration, $scheduled_price]);

            $message = 'Запись успешно добавлена.';
            $messageType = 'success';
            $service_id = $car_category_id = $client_name = $client_phone = $car_model = $car_license_plate = $appointment_date = $appointment_time = '';
        } catch (Exception $e) {
            $message = 'Ошибка при добавлении записи: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Пожалуйста, заполните все обязательные поля.';
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
    <title>Добавить Запись - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select, input[type="text"], input[type="tel"], input[type="date"], input[type="time"] { width: 100%; padding: 8px; box-sizing: border-box; }
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

    <h1>Добавить Запись - <?= formatFullName($employee_info['first_name'], $employee_info['last_name']) ?></h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
        <div class="form-group">
            <label for="service_id">Услуга *:</label>
            <select id="service_id" name="service_id" required>
                <option value="">Выберите услугу...</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>" <?= ($service['id'] == $service_id && in_array($service['id'], $spec_ids)) ? 'selected' : '' ?> <?= !in_array($service['id'], $spec_ids) ? 'disabled' : '' ?>>
                        <?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>
                        <?= !in_array($service['id'], $spec_ids) ? ' (Не специализация)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="car_category_id">Категория автомобиля *:</label>
            <select id="car_category_id" name="car_category_id" required>
                <option value="">Выберите категорию...</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($category['id'] == $car_category_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="client_name">Имя клиента *:</label>
            <input type="text" id="client_name" name="client_name" value="<?= htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="client_phone">Телефон клиента:</label>
            <input type="tel" id="client_phone" name="client_phone" value="<?= htmlspecialchars($client_phone, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label for="car_model">Модель автомобиля *:</label>
            <input type="text" id="car_model" name="car_model" value="<?= htmlspecialchars($car_model, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="car_license_plate">Номерной знак:</label>
            <input type="text" id="car_license_plate" name="car_license_plate" value="<?= htmlspecialchars($car_license_plate, ENT_QUOTES, 'UTF-8') ?>" maxlength="20">
        </div>
        <div class="form-group">
            <label for="appointment_date">Дата записи *:</label>
            <input type="date" id="appointment_date" name="appointment_date" value="<?= $appointment_date ?>" required>
        </div>
        <div class="form-group">
            <label for="appointment_time">Время записи *:</label>
            <input type="time" id="appointment_time" name="appointment_time" value="<?= $appointment_time ?>" required>
        </div>
        <button type="submit" class="btn">Сохранить</button>
        <a href="employee_schedule.php?employee_id=<?= $employee_id ?>" class="btn btn-cancel">Отмена</a>
    </form>

</body>
</html>