<?php
require_once 'includes/database.php';
require_once 'includes/functions.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: " . (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? "/admin/" : "/"));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = authenticateUser($email, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        header("Location: " . ($user['is_admin'] ? "/admin/" : "/"));
        exit;
    } else {
        $error = "Неверный email или пароль.";
    }
}

require_once 'includes/header.php';
?>

<section class="auth-form">
    <h2>Вход</h2>
    
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" name="login" class="btn">Войти</button>
    </form>
    
    <p>Еще нет аккаунта? <a href="/register.php">Зарегистрируйтесь</a></p>
</section>

<?php
require_once 'includes/footer.php';
?>