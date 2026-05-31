<?php
// fallback.php

require_once 'config.php';

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $wishes = $_POST['wishes'] ?? '';

    // Валидация
    if ($error = validateName($name)) $errors['name'] = $error;
    if ($error = validatePhone($phone)) $errors['phone'] = $error;
    if ($error = validateEmail($email)) $errors['email'] = $error;

    if (empty($errors)) {
        // Проверка дубликата
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id FROM tutor_requests WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors['email'] = 'Этот email уже зарегистрирован';
        } else {
            // Генерация логина и пароля
            $login = generateLogin($name, $phone);
            $password = generatePassword();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Сохранение в БД
            $stmt = $pdo->prepare("
                INSERT INTO tutor_requests
                (name, phone, email, subject, wishes, login, password_hash, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'new')
            ");
            $stmt->execute([$name, $phone, $email, $subject, $wishes, $login, $passwordHash]);

            $message = "
                <h3>✅ Заявка успешно создана!</h3>
                <p><strong>Ваш логин:</strong> $login</p>
                <p><strong>Ваш пароль:</strong> $password</p>
                <p style='margin-top: 15px;'>
                    <a href='edit.php'>Перейти в личный кабинет →</a>
                </p>
                <p style='margin-top: 10px; color: #666; font-size: 0.9em;'>
                    ️ Сохраните логин и пароль!
                </p>
            ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявка (без JavaScript) — TutorMatch</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🎓 TutorMatch</h1>
            <p>Резервная форма (JavaScript отключен)</p>
        </div>
    </header>

    <main class="container">
        <div class="form-section">
            <h2>Форма заявки на подбор репетитора</h2>
            <p style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
                ⚠️ Это резервная форма для браузеров без JavaScript.
                <a href="index.html">Вернуться к основной версии</a>
            </p>

            <?php if ($message): ?>
                <div class="result-box success">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="fallback.php">
                <div class="form-group">
                    <label for="name">Ваше имя *</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <span style="color: red;"><?= $errors['name'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    <?php if (isset($errors['phone'])): ?>
                        <span style="color: red;"><?= $errors['phone'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <span style="color: red;"><?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="subject">Предмет</label>
                    <select id="subject" name="subject">
                        <option value="">Выберите предмет</option>
                        <option value="math" <?= ($_POST['subject'] ?? '') === 'math' ? 'selected' : '' ?>>Математика</option>
                        <option value="english" <?= ($_POST['subject'] ?? '') === 'english' ? 'selected' : '' ?>>Английский язык</option>
                        <option value="physics" <?= ($_POST['subject'] ?? '') === 'physics' ? 'selected' : '' ?>>Физика</option>
                        <option value="chemistry" <?= ($_POST['subject'] ?? '') === 'chemistry' ? 'selected' : '' ?>>Химия</option>
                        <option value="russian" <?= ($_POST['subject'] ?? '') === 'russian' ? 'selected' : '' ?>>Русский язык</option>
                        <option value="history" <?= ($_POST['subject'] ?? '') === 'history' ? 'selected' : '' ?>>История</option>
                        <option value="programming" <?= ($_POST['subject'] ?? '') === 'programming' ? 'selected' : '' ?>>Программирование</option>
                        <option value="other" <?= ($_POST['subject'] ?? '') === 'other' ? 'selected' : '' ?>>Другой</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="wishes">Пожелания к репетитору</label>
                    <textarea id="wishes" name="wishes" rows="4"><?= htmlspecialchars($_POST['wishes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-primary">Отправить заявку</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 TutorMatch. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>
