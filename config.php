<?php
// config.php

// Подключение к БД
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=u82579;charset=utf8mb4',
            'u82579',
            '1953280',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
    return $pdo;
}

// Валидация имени
function validateName(string $name): ?string {
    if (empty($name)) return 'Имя обязательно для заполнения';
    if (strlen($name) < 2) return 'Имя слишком короткое (минимум 2 символа)';
    if (strlen($name) > 150) return 'Имя слишком длинное (максимум 150 символов)';
    if (!preg_match('/^[а-яёa-z\s\-]+$/iu', $name)) {
              return 'Имя может содержать только буквы, пробелы и дефисы';
    }
    return null;
}

// Валидация телефона
function validatePhone(string $phone): ?string {
    if (empty($phone)) return 'Телефон обязателен для заполнения';
    if (!preg_match('/^\+7\d{10}$/', $phone)) {
        return 'Телефон должен быть в формате +7XXXXXXXXXX (например: +79991234567)';
    }
    return null;
}

// Валидация email
function validateEmail(string $email): ?string {
    if (empty($email)) return 'Email обязателен для заполнения';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Введите корректный email адрес';
    }
    return null;

}

function generateLogin(string $name, string $phone): string {
    // Очищаем имя: убираем пробелы, спецсимволы, приводим к нижнему регистру
    $cleanName = strtolower(trim($name));
    $cleanName = preg_replace('/[^a-zа-яё0-9]/u', '', $cleanName);

    // Берем первые 5 символов (стандартный substr)
    // Если имя на кириллице, просто берем первые байты (это ок для логина)
    $namePart = substr($cleanName, 0, 5);

    // Последние 4 цифры телефона
    $phonePart = substr($phone, -4);

    // Случайное число
    $random = rand(100, 999);

    return "{$namePart}_{$phonePart}_{$random}";
}
// Генерация случайного пароля
function generatePassword(int $length = 8): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}
?>
