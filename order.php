<?php
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

require_once 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /profile.php");
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$sql = "SELECT o.*, p.status as payment_status, p.transaction_id, p.payment_method 
        FROM orders o
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.id = ?";
        
if (!isAdmin($user_id)) {
    $sql .= " AND o.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
}

$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: /profile.php");
    exit;
}

$order_items = getOrderItems($order_id);
$activation_keys = getActivationKeys($order_id);
?>

<section class="order-details">
    <h2><?= t('order_details') ?> #<?= $order['id'] ?></h2>
    
    <div class="order-info">
        <p><strong><?= t('order_date') ?>:</strong> <?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></p>
        <p><strong><?= t('status') ?>:</strong> 
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
        </p>
        <p><strong><?= t('amount') ?>:</strong> <?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></p>
        
        <?php if ($order['payment_status']): ?>
            <p><strong><?= t('payment_method') ?>:</strong> 
                <?= t(ucfirst($order['payment_method'] ?? 'unknown')) ?> 
                (<?= t($order['payment_status']) ?>)
            </p>
            <?php if ($order['transaction_id']): ?>
                <p><strong><?= t('transaction_id') ?>:</strong> <?= htmlspecialchars($order['transaction_id']) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="order-items">
        <h3><?= t('items') ?></h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?= t('game') ?></th>
                    <th><?= t('price') ?></th>
                    <th><?= t('quantity') ?></th>
                    <th><?= t('total') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <img src="/assets/images/games/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" width="50" alt="<?= htmlspecialchars($item['title']) ?>">
                            <?= htmlspecialchars($item['title']) ?>
                        </td>
                        <td><?= number_format($item['price'], 2) ?> <?= t('currency') ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'] * $item['quantity'], 2) ?> <?= t('currency') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong><?= t('total') ?>:</strong></td>
                    <td><strong><?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <?php if ($activation_keys): ?>
        <div class="activation-keys">
            <h3><?= t('activation_keys') ?></h3>
            <?php foreach ($activation_keys as $key): ?>
                <div class="key-item">
                    <p><strong><?= t('game') ?>:</strong> <?= htmlspecialchars($key['title']) ?></p>
                    <p><strong><?= t('key') ?>:</strong> <?= htmlspecialchars($key['activation_key']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($order['payment_status'] == 'pending' && !isAdmin($user_id)): ?>
        <div class="order-actions">
            <a href="/payment.php" class="btn"><?= t('pay_order') ?></a>
        </div>
    <?php endif; ?>
    
    <div class="back-link">
        <a href="/profile.php" class="btn"><?= t('back_to_orders') ?></a>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>