<?php
require_once '../includes/functions.php';
require_once 'admin_functions.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $user = authenticateUser($_SESSION['user_email'], $current_password);
    
    if (!$user) {
        $error = t('Invalid current password.');
    } elseif ($new_password !== $confirm_password) {
        $error = t('New passwords do not match.');
    } elseif (strlen($new_password) < 8) {
        $error = t('New password must be at least 8 characters long.');
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = t('Password changed successfully.');
            logAdminAction($user_id, 'change_password');
        } else {
            $error = t('Error changing password.');
        }
    }
}

require_once '../includes/header.php';
?>

<section class="admin-password">
    <h2><?= t('change_password') ?></h2>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif ?>
    
    <form method="post">
        <div class="form-group">
            <label for="current_password"><?= t('current_password') ?>:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password"><?= t('new_password') ?>:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password"><?= t('confirm_password') ?>:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <button type="submit" class="btn"><?= t('change_password') ?></button>
        <a href="/admin/profile.php" class="btn btn-outline"><?= t('cancel') ?></a>
    </form>
</section>

<?php
require_once '../includes/footer.php';
?>