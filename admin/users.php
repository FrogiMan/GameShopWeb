<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

// Handle error/success messages
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Обработка форм
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header("Location: users.php?error=" . urlencode(t('csrf_validation_failed')));
        exit;
    }
    
    if (isset($_POST['add_user'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $email, $password, $is_admin);
            if ($stmt->execute()) {
                logAdminAction($_SESSION['user_id'], 'add_user', "Added user: $email");
                header("Location: users.php?success=" . urlencode(t('user_added')));
            } else {
                throw new Exception("Failed to add user");
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            header("Location: users.php?error=" . urlencode(t('user_add_failed')));
        }
        exit;
    }
    elseif (isset($_POST['edit_user'])) {
        $user_id = (int)$_POST['user_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        try {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, is_admin = ? WHERE id = ?");
            $stmt->bind_param("ssii", $name, $email, $is_admin, $user_id);
            if ($stmt->execute()) {
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $password, $user_id);
                    $stmt->execute();
                }
                logAdminAction($_SESSION['user_id'], 'edit_user', "Edited user ID: $user_id");
                header("Location: users.php?success=" . urlencode(t('user_updated')));
            } else {
                throw new Exception("Failed to update user");
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            header("Location: users.php?error=" . urlencode(t('user_update_failed')));
        }
        exit;
    }
}

// Обработка удаления
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        header("Location: users.php?error=" . urlencode(t('csrf_validation_failed')));
        exit;
    }
    
    $user_id = (int)$_GET['delete'];
    // Нельзя удалить себя
    if ($user_id != $_SESSION['user_id']) {
        if (deleteUser($user_id)) {
            logAdminAction($_SESSION['user_id'], 'delete_user', "Deleted user ID: $user_id");
            header("Location: users.php?success=" . urlencode(t('user_deleted')));
        } else {
            header("Location: users.php?error=" . urlencode(t('user_delete_failed')));
        }
    } else {
        header("Location: users.php?error=" . urlencode(t('cannot_delete_self')));
    }
    exit;
}

// Получаем всех пользователей
$stmt = $conn->query("SELECT * FROM users ORDER BY is_admin DESC, name ASC");
$users = $stmt->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<section class="admin-users">
    <h2><?= t('manage_users') ?></h2>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="admin-actions">
        <button class="btn" onclick="document.getElementById('add-user-modal').style.display='block'"><?= t('add_user') ?></button>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= t('name') ?></th>
                    <th>Email</th>
                    <th><?= t('role') ?></th>
                    <th><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?= $user['is_admin'] ? t('admin') : t('user') ?>
                        </td>
                        <td class="actions">
                            <button class="btn btn-sm" 
                                    onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>', '<?= htmlspecialchars($user['email']) ?>', <?= $user['is_admin'] ?>)">
                                <?= t('edit') ?>
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?= $user['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('<?= t('confirm_delete_user') ?>')">
                                    <?= t('delete') ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Модальное окно добавления пользователя -->
<div id="add-user-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('add-user-modal').style.display='none'">×</span>
        <h3><?= t('add_user') ?></h3>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="form-group">
                <label for="add-name"><?= t('name') ?>:</label>
                <input type="text" id="add-name" name="name" required>
            </div>
            <div class="form-group">
                <label for="add-email">Email:</label>
                <input type="email" id="add-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="add-password"><?= t('password') ?>:</label>
                <input type="password" id="add-password" name="password" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_admin"> <?= t('admin_role') ?>
                </label>
            </div>
            <button type="submit" name="add_user" class="btn"><?= t('add_user') ?></button>
        </form>
    </div>
</div>

<!-- Модальное окно редактирования пользователя -->
<div id="edit-user-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('edit-user-modal').style.display='none'">×</span>
        <h3><?= t('edit_user') ?></h3>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" id="edit-user-id" name="user_id">
            <div class="form-group">
                <label for="edit-name"><?= t('name') ?>:</label>
                <input type="text" id="edit-name" name="name" required>
            </div>
            <div class="form-group">
                <label for="edit-email">Email:</label>
                <input type="email" id="edit-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="edit-password"><?= t('new_password') ?> (<?= t('leave_blank') ?>):</label>
                <input type="password" id="edit-password" name="password">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit-is-admin" name="is_admin"> <?= t('admin_role') ?>
                </label>
            </div>
            <button type="submit" name="edit_user" class="btn"><?= t('save_changes') ?></button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, email, isAdmin) {
    document.getElementById('edit-user-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-is-admin').checked = isAdmin;
    document.getElementById('edit-user-modal').style.display = 'block';
}

// Закрытие модальных окон при клике вне их
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}
</script>
<?php
require_once '../includes/footer.php';
?>