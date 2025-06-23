<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.1 404 Not Found");
    die(t('invalid_order_id'));
}

$order_id = (int)$_GET['id'];
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email, p.status as payment_status 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("HTTP/1.1 404 Not Found");
    die(t('order_not_found'));
}

$items = getOrderItems($order_id);
$activation_keys = getActivationKeys($order_id);

logAdminAction($_SESSION['user_id'], 'view_order_details', "Order ID: $order_id");

require_once '../includes/header.php';
?>

<section class="admin-order-details">
    <h2><?= t('order_details') ?> #<?= $order['id'] ?></h2>
    
    <div class="order-info">
        <h3><?= t('order_info') ?></h3>
        <p><strong><?= t('customer') ?>:</strong> <?= htmlspecialchars($order['user_name']) ?> (<?= htmlspecialchars($order['user_email']) ?>)</p>
        <p><strong><?= t('date') ?>:</strong> <?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></p>
        <p><strong><?= t('amount') ?>:</strong> <?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></p>
        <p><strong><?= t('status') ?>:</strong> 
            <span class="status-badge status-<?= strtolower($order['payment_status'] ?? 'none') ?>">
                <?= t($order['payment_status'] ?? 'no_payment') ?>
            </span>
        </p>
    </div>
    
    <div class="order-items">
        <h3><?= t('items') ?></h3>
        <?php if ($items): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= t('game') ?></th>
                        <th><?= t('quantity') ?></th>
                        <th><?= t('price') ?></th>
                        <th><?= t('total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <img src="/assets/images/games/<?= htmlspecialchars($item['image']) ?>" width="50" alt="<?= htmlspecialchars($item['title']) ?>">
                                <?= htmlspecialchars($item['title']) ?>
                            </td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 2) ?> <?= t('currency') ?></td>
                            <td><?= number_format($item['quantity'] * $item['price'], 2) ?> <?= t('currency') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?= t('no_items') ?></p>
        <?php endif; ?>
    </div>
    
    <div class="activation-keys">
        <h3><?= t('activation_keys') ?></h3>
        <?php if ($activation_keys): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= t('game') ?></th>
                        <th><?= t('key') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activation_keys as $key): ?>
                        <tr>
                            <td><?= htmlspecialchars($key['title']) ?></td>
                            <td><?= htmlspecialchars($key['activation_key']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?= t('no_activation_keys') ?></p>
        <?php endif; ?>
    </div>
    
    <div class="actions">
        <a href="orders.php" class="btn btn-outline"><?= t('back_to_orders') ?></a>
        <form method="post" action="update_order_status.php" class="status-form">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <select name="status" onchange="this.form.submit()">
                <option value="pending" <?= ($order['payment_status'] ?? '') == 'pending' ? 'selected' : '' ?>><?= t('pending') ?></option>
                <option value="completed" <?= ($order['payment_status'] ?? '') == 'completed' ? 'selected' : '' ?>><?= t('completed') ?></option>
                <option value="failed" <?= ($order['payment_status'] ?? '') == 'failed' ? 'selected' : '' ?>><?= t('failed') ?></option>
            </select>
        </form>
    </div>
</section>

<style>
.admin-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.admin-table th, .admin-table td { padding: 10px; border: 1px solid #ddd; }
.status-badge { padding: 5px 10px; border-radius: 4px; color: white; }
.status-pending { background: #ffc107; }
.status-completed { background: #28a745; }
.status-failed { background: #dc3545; }
.status-none { background: #6c757d; }
.order-info, .order-items, .activation-keys { margin-bottom: 30px; }
.actions { display: flex; gap: 10px; align-items: center; }
.status-form { display: inline-block; }
</style>

<?php
require_once '../includes/footer.php';
?>