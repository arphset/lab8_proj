<?php
// api.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

// Получаем данные из тела запроса
$input = json_decode(file_get_contents('php://input'), true);

$name = $input['name'] ?? '';
$phone = $input['phone'] ?? '';
$email = $input['email'] ?? '';
$subject = $input['subject'] ?? '';
$wishes = $input['wishes'] ?? '';

// Валидация на сервере
$errors = [];
if ($error = validateName($name)) $errors['name'] = $error;
if ($error = validatePhone($phone)) $errors['phone'] = $error;
if ($error = validateEmail($email)) $errors['email'] = $error;

// Если есть ошибки - возвращаем их
if (!empty($errors)) {
    echo json_encode([        'success' => false,
        'errors' => $errors
    ]);
    exit;
}

// Проверка на дубликат email
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id FROM tutor_requests WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode([
        'success' => false,
        'errors' => ['email' => 'Этот email уже зарегистрирован']
    ]);
    exit;
}

// Генерируем логин и пароль
$login = generateLogin($name, $phone);
$password = generatePassword();
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Сохраняем в БД
$stmt = $pdo->prepare("
    INSERT INTO tutor_requests
    (name, phone, email, subject, wishes, login, password_hash, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'new')
");
$stmt->execute([$name, $phone, $email, $subject, $wishes, $login, $passwordHash]);

// Возвращаем успех
echo json_encode([
    'success' => true,
    'login' => $login,
    'password' => $password,
    'message' => 'Заявка на подбор репетитора успешно создана!'
]);
?>
