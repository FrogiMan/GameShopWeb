<?php
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$orders = getUserOrders($user_id);
$wishlistItems = getWishlistItems($user_id);
$notifications = getUserNotifications($user_id);
$order_success = isset($_GET['order_success']) ? (int)$_GET['order_success'] : null;
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_push_token'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $push_token = trim($_POST['push_token']);
    $stmt = $conn->prepare("UPDATE users SET push_token = ? WHERE id = ?");
    $stmt->bind_param("si", $push_token, $user_id);
    $stmt->execute();
    $success_message = t('profile_updated');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $notification_id = (int)$_POST['notification_id'];
    if (deleteNotification($notification_id, $user_id)) {
        $success_message = t('notification_deleted');
        header("Location: /profile.php");
        exit;
    } else {
        $error_message = t('complaint_error');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $order_id = (int)$_POST['order_id'];
    $stmt = $conn->prepare("SELECT p.status FROM orders o LEFT JOIN payments p ON o.id = p.order_id WHERE o.id = ? AND o.user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if ($order && $order['status'] == 'completed') {
        if (deleteOrder($order_id, $user_id)) {
            $success_message = t('order_deleted');
            header("Location: /profile.php");
            exit;
        } else {
            $error_message = t('complaint_error');
        }
    } else {
        $error_message = t('complaint_error');
    }
}

require_once 'includes/header.php';
?>

<section class="profile">
    <h2><?= t('your_profile') ?></h2>
    
    <?php if ($order_success): ?>
        <div class="success">
            <?= t('order_success', ['order_id' => $order_success]) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <div class="profile-info">
        <h3><?= t('personal_info') ?></h3>
        <p><strong><?= t('name') ?>:</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p><strong><?= t('email') ?>:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <div class="profile-actions">
            <a href="/edit_profile.php" class="btn"><?= t('edit_profile') ?></a>
            <a href="/change_password.php" class="btn"><?= t('change_password') ?></a>
        </div>
    </div>
    
    <div class="notifications">
        <h3><?= t('notifications') ?></h3>
        <?php if (count($notifications) > 0): ?>
            <table class="notifications-table">
                <thead>
                    <tr>
                        <th><?= t('title') ?></th>
                        <th><?= t('message') ?></th>
                        <th><?= t('date') ?></th>
                        <th><?= t('status') ?></th>
                        <th><?= t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $notification): ?>
                        <tr>
                            <td><?= htmlspecialchars($notification['title']) ?></td>
                            <td><?= nl2br(htmlspecialchars($notification['message'])) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($notification['created_at'])) ?></td>
                            <td><?= $notification['is_read'] ? t('read') : t('unread') ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('<?= t('delete_notification_confirm') ?>')">
                                    <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <button type="submit" name="delete_notification" class="btn btn-outline"><?= t('delete_notification') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?= t('no_notifications') ?></p>
        <?php endif; ?>
        
        <h3><?= t('enable_notifications') ?></h3>
        <form method="post">
            <label><input type="checkbox" id="enable-notifications" onchange="requestNotificationPermission()"> <?= t('enable_notifications') ?></label>
            <input type="hidden" id="push-token" name="push_token">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <button type="submit" name="update_push_token" class="btn"><?= t('save') ?></button>
        </form>
    </div>
    
    <div class="wishlist">
        <h3><?= t('wishlist') ?></h3>
        <?php if (count($wishlistItems) > 0): ?>
            <div class="games-grid">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="game-card">
                        <img src="/assets/images/games/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="price"><?= htmlspecialchars($item['price']) ?> <?= t('currency') ?></p>
                        <form method="post" action="/wishlist_action.php">
                            <input type="hidden" name="game_id" value="<?= $item['game_id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <button type="submit" name="remove_from_wishlist" class="btn btn-outline"><?= t('remove_from_wishlist') ?></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?= t('empty_wishlist') ?> <a href="/catalog.php"><?= t('go_to_catalog') ?></a></p>
        <?php endif; ?>
    </div>
    
    <div class="orders-history">
        <h3><?= t('order_history') ?></h3>
        <?php if (count($orders) > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th><?= t('order_number') ?></th>
                        <th><?= t('date') ?></th>
                        <th><?= t('amount') ?></th>
                        <th><?= t('status') ?></th>
                        <th><?= t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></td>
                            <td>
                                <?php 
                                if ($order['payment_status'] == 'completed') {
                                    echo '<span class="status-completed">' . t('completed') . '</span>';
                                } elseif ($order['payment_status'] == 'pending') {
                                    echo '<span class="status-pending">' . t('pending') . '</span>';
                                } elseif ($order['payment_status'] == 'failed') {
                                    echo '<span class="status-failed">' . t('failed') . '</span>';
                                } else {
                                    echo '<span class="status-none">' . t('no_payment') . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="/order.php?id=<?= $order['id'] ?>" class="btn"><?= t('details') ?></a>
                                <?php if ($order['payment_status'] == 'completed'): ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('<?= t('delete_order_confirm') ?>')">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <button type="submit" name="delete_order" class="btn btn-outline"><?= t('delete_order') ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?= t('no_orders') ?> <a href="/catalog.php"><?= t('go_to_catalog') ?></a></p>
        <?php endif; ?>
    </div>
</section>

<script>
function requestNotificationPermission() {
    if ('Notification' in window && 'serviceWorker' in navigator) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: 'your_vapid_public_key'
                    }).then(subscription => {
                        document.getElementById('push-token').value = JSON.stringify(subscription);
                    }).catch(error => {
                        console.error('Push subscription failed:', error);
                    });
                });
            }
        });
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>