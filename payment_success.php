<?php
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header("Location: /profile.php");
    exit;
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT o.*, p.transaction_id 
                       FROM orders o
                       LEFT JOIN payments p ON o.id = p.order_id
                       WHERE o.id = ? AND o.user_id = ? AND o.status = 'completed'");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: /profile.php");
    exit;
}

$activation_keys = getActivationKeys($order_id);

require_once 'includes/header.php';
?>

<section class="payment-success">
    <div class="success-message">
        <h2><?= t('payment_success') ?></h2>
        <p><?= t('payment_success_message') ?></p>
        
        <div class="order-details">
            <h3><?= t('order_details') ?></h3>
            <p><strong><?= t('order_number') ?>:</strong> #<?= $order['id'] ?></p>
            <p><strong><?= t('transaction_id') ?>:</strong> <?= htmlspecialchars($order['transaction_id'] ?? 'N/A') ?></p>
            <p><strong><?= t('amount') ?>:</strong> <?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></p>
            <p><strong><?= t('date') ?>:</strong> <?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></p>
        </div>
        
        <div class="activation-keys">
            <h3><?= t('activation_keys') ?></h3>
            <?php if ($activation_keys): ?>
                <?php foreach ($activation_keys as $key): ?>
                    <div class="key-item">
                        <p><strong><?= t('game') ?>:</strong> <?= htmlspecialchars($key['title']) ?></p>
                        <p><strong><?= t('key') ?>:</strong> <?= htmlspecialchars($key['activation_key']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?= t('no_activation_keys') ?></p>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <a href="/profile.php" class="btn"><?= t('my_orders') ?></a>
            <a href="/catalog.php" class="btn"><?= t('continue_shopping') ?></a>
        </div>
    </div>
</section>

<style>
.payment-success { margin: 20px auto; max-width: 800px; text-align: center; }
.success-message { padding: 20px; background: #d4edda; border-radius: 8px; }
.order-details, .activation-keys { margin: 20px 0; text-align: left; }
.key-item { padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; border-radius: 4px; }
.actions { display: flex; gap: 10px; justify-content: center; }
</style>

<?php
require_once 'includes/footer.php';
?>