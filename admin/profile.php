<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        if (empty($name) || empty($email)) {
            $error = t('all_fields_required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = t('invalid_email');
        } elseif ($email != $user['email'] && emailExists($email)) {
            $error = t('email_exists');
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = t('profile_updated');
                logAdminAction($user_id, 'update_profile', "Updated name: $name, email: $email");
            } else {
                $error = t('profile_update_error');
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $auth_user = authenticateUser($user['email'], $current_password);
        
        if (!$auth_user) {
            $error = t('invalid_current_password');
        } elseif ($new_password !== $confirm_password) {
            $error = t('passwords_not_match');
        } elseif (strlen($new_password) < 6) {
            $error = t('password_too_short');
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = t('password_updated');
                logAdminAction($user_id, 'change_password');
            } else {
                $error = t('password_update_error');
            }
        }
    }
}

require_once '../includes/header.php';
?>

<section class="admin-profile">
    <h2><?= t('admin_profile') ?></h2>
    
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="profile-form">
        <h3><?= t('personal_info') ?></h3>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="form-group">
                <label for="name"><?= t('name') ?>:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email"><?= t('email') ?>:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn"><?= t('save') ?></button>
        </form>
    </div>
    
    <div class="password-form">
        <h3><?= t('change_password') ?></h3>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
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
            <button type="submit" name="change_password" class="btn"><?= t('change_password') ?></button>
        </form>
    </div>
</section>


<?php
require_once '../includes/footer.php';
?>