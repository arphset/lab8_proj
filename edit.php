<?php
// edit.php

session_start();
require_once 'config.php';

// Если пользователь не авторизован - показываем форму входа
if (!isset($_SESSION['user_id'])) {
    // Обработка входа
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM tutor_requests WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: edit.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
          }

    // Показываем форму входа
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Вход — TutorMatch</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <div class="container">
                <h1>🎓 TutorMatch</h1>
                <p>Личный кабинет</p>
            </div>
        </header>

        <main class="container">
            <div class="form-section" style="max-width: 500px; margin: 40px auto;">
                <h2>Вход в личный кабинет</h2>
                  <?php if (isset($error)): ?>
                    <div class="result-box error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="login">Логин</label>
                        <input type="text" id="login" name="login" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn-primary">Войти</button>
                </form>

                <p style="margin-top: 20px; text-align: center;">
                    <a href="index.html">← Вернуться на главную</a>
                </p>
            </div>
        </main>
      </body>
    </html>
    <?php
    exit;
}

// Если авторизован - показываем личный кабинет
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM tutor_requests WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: edit.php');
    exit;
}

// Обработка обновления данных
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $wishes = $_POST['wishes'] ?? '';

    // Валидация
    $errors = [];
    if ($error = validateName($name)) $errors['name'] = $error;
    if ($error = validatePhone($phone)) $errors['phone'] = $error;

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE tutor_requests
            SET name = ?, phone = ?, subject = ?, wishes = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $subject, $wishes, $_SESSION['user_id']]);
        $message = '✅ Данные успешно обновлены!';

        // Обновляем данные пользователя
        $user['name'] = $name;
        $user['phone'] = $phone;
        $user['subject'] = $subject;
        $user['wishes'] = $wishes;
    }
}
// Статусы заявки
$statuses = [
    'new' => ' Новая заявка',
    'processing' => '⏳ В обработке',
    'matched' => '✅ Репетитор подобран',
    'completed' => '✔️ Завершено'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — TutorMatch</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🎓 TutorMatch</h1>
            <p>Личный кабинет</p>
        </div>
    </header>

    <main class="container">
        <div class="form-section">
            <h2>Привет, <?= htmlspecialchars($user['name']) ?>! 👋</h2>

            <?php if ($message): ?>
                <div class="result-box success">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div style="background: #f0f4ff; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
                <h3>Статус вашей заявки</h3>
                <p style="font-size: 1.2em; font-weight: bold; color: #667eea;">
                    <?= $statuses[$user['status']] ?? ' Новая заявка' ?>
                </p>
                <p style="color: #666; margin-top: 10px;">
                    Дата создания: <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?>
                </p>
            </div>

            <h3>Редактирование данных</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Ваше имя *</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($user['name']) ?>" required>
 </div>

                <div class="form-group">
                    <label for="email">Email (нельзя изменить)</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="subject">Предмет</label>
                    <select id="subject" name="subject">
                        <option value="">Выберите предмет</option>
                        <option value="math" <?= $user['subject'] === 'math' ? 'selected' : '' ?>>Математика</option>
                        <option value="english" <?= $user['subject'] === 'english' ? 'selected' : '' ?>>Английский язык</option>
                        <option value="physics" <?= $user['subject'] === 'physics' ? 'selected' : '' ?>>Физика</option>
                        <option value="chemistry" <?= $user['subject'] === 'chemistry' ? 'selected' : '' ?>>Химия</option>
                        <option value="russian" <?= $user['subject'] === 'russian' ? 'selected' : '' ?>>Русский язык</option>
                        <option value="history" <?= $user['subject'] === 'history' ? 'selected' : '' ?>>История</option>
                        <option value="programming" <?= $user['subject'] === 'programming' ? 'selected' : '' ?>>Программирование</option>
                        <option value="other" <?= $user['subject'] === 'other' ? 'selected' : '' ?>>Другой</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="wishes">Пожелания к репетитору</label>
                    <textarea id="wishes" name="wishes" rows="4"><?= htmlspecialchars($user['wishes']) ?></textarea>
                </div>

                <button type="submit" class="btn-primary">Сохранить изменения</button>
            </form>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
                <a href="logout.php" style="color: #dc3545; text-decoration: none;">🚪 Выйти из личного кабинета</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 TutorMatch. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>
