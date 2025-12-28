<?php

include_once __DIR__ . '/../config/config.php';

try {
    $sql_employees = "
        SELECT e.id, e.first_name, e.last_name, e.phone, e.email, e.hire_date, e.dismissal_date, e.salary_percentage, e.is_active
        FROM Employee e
        WHERE e.is_active = 1
        ORDER BY e.last_name, e.first_name
    ";
    $stmt = $pdo->prepare($sql_employees);
    $stmt->execute();
    $employees = $stmt->fetchAll();

    $employees_with_specs = [];
    foreach ($employees as $emp) {
        $emp_id = $emp['id'];
        $sql_specs = "
            SELECT s.name
            FROM EmployeeSpecialization es
            JOIN Service s ON es.service_id = s.id
            WHERE es.employee_id = ?
        ";
        $stmt_specs = $pdo->prepare($sql_specs);
        $stmt_specs->execute([$emp_id]);
        $specs = $stmt_specs->fetchAll(PDO::FETCH_COLUMN);
        $emp['specializations'] = implode(', ', $specs);
        $employees_with_specs[] = $emp;
    }

} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

function formatFullName($first_name, $last_name) {
    $full_name = $last_name . ' ' . $first_name;
    return htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>СТО - Список Активных Сотрудников</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions { white-space: nowrap; }
        .btn { margin: 0 2px; padding: 4px 8px; text-decoration: none; border: 1px solid #ccc; background-color: #f9f9f9; color: #333; cursor: pointer; }
        .btn:hover { background-color: #e0e0e0; }
        .btn-add { display: inline-block; margin-top: 10px; padding: 8px 16px; background-color: #4CAF50; color: white; text-decoration: none; }
        .btn-add:hover { background-color: #45a049; }
        .spec-list { font-size: 0.9em; color: #555; }
    </style>
</head>
<body>

    <h1>Список Активных Сотрудников СТО</h1>

    <table>
        <thead>
            <tr>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Процент от выручки</th>
                <th>Специализации</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($employees_with_specs) > 0): ?>
                <?php foreach ($employees_with_specs as $employee): ?>
                    <tr>
                        <td><?= formatFullName($employee['first_name'], $employee['last_name']) ?></td>
                        <td><?= htmlspecialchars($employee['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($employee['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($employee['salary_percentage'], ENT_QUOTES, 'UTF-8') ?>%</td>
                        <td class="spec-list"><?= htmlspecialchars($employee['specializations'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="actions">
                            <a class="btn" href="edit_employee.php?id=<?= $employee['id'] ?>">Редактировать</a>
                            <a class="btn" href="delete_employee.php?id=<?= $employee['id'] ?>">Уволить</a>
                            <a class="btn" href="employee_schedule.php?employee_id=<?= $employee['id'] ?>">Записи</a>
                            <a class="btn" href="employee_works.php?employee_id=<?= $employee['id'] ?>">Выполненные работы</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Активных сотрудников пока нет.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a class="btn-add" href="add_employee.php">Добавить Сотрудника</a>

</body>
</html>