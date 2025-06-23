<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $user = authenticateUser($_SESSION['user_email'], $current_password);
    
    if (!$user) {
        $error = "Текущий пароль неверный.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Новые пароли не совпадают.";
    } elseif (strlen($new_password) < 6) {
        $error = "Новый пароль должен содержать не менее 6 символов.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = "Пароль успешно изменен.";
        } else {
            $error = "Ошибка при изменении пароля.";
        }
    }
}
?>

<section class="auth-form">
    <h2>Изменить пароль</h2>
    
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="current_password">Текущий пароль:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">Новый пароль:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Подтвердите новый пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn">Изменить пароль</button>
        <a href="/profile.php" class="btn btn-outline">Отмена</a>
    </form>
</section>

<?php
require_once 'includes/footer.php';
?>