<?php
// register.php
session_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Check if user is already logged in before any output
if (isset($_SESSION['user_id'])) {
    header("Location: /profile.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валидация
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Все поля обязательны для заполнения.";
    } elseif ($password !== $confirm_password) {
        $error = "Пароли не совпадают.";
    } elseif (strlen($password) < 6) {
        $error = "Пароль должен содержать не менее 6 символов.";
    } elseif (emailExists($email)) {
        $error = "Этот email уже зарегистрирован.";
    } else {
        // Регистрация пользователя
        if (registerUser($name, $email, $password)) {
            // Автоматический вход после регистрации
            $user = authenticateUser($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                header("Location: /profile.php");
                exit;
            } else {
                $error = "Ошибка входа после регистрации. Пожалуйста, войдите вручную.";
            }
        } else {
            $error = "Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.";
        }
    }
}

// Include header after all redirects
require_once 'includes/header.php';
?>

<section class="auth-form">
    <h2>Регистрация</h2>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="name">Имя:</label>
            <input type="text" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Подтвердите пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" name="register" class="btn">Зарегистрироваться</button>
    </form>
    
    <p>Уже есть аккаунт? <a href="/login.php">Войдите</a></p>
</section>

<?php
require_once 'includes/footer.php';
?>