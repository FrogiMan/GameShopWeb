<?php
ob_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_order_id'])) {
    header("Location: /cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_SESSION['pending_order_id'];
$payment_method = $_SESSION['pending_payment_method'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: /cart.php");
    exit;
}

$stmt = $conn->prepare("SELECT oi.*, g.title FROM order_items oi 
                       JOIN games g ON oi.game_id = g.id 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = t('csrf_validation_failed');
    } else {
        $posted_payment_method = $_POST['payment_method'] ?? $payment_method;
        
        if ($posted_payment_method === 'credit_card') {
            $card_number = isset($_POST['card_number']) ? preg_replace('/\D/', '', $_POST['card_number']) : '';
            $card_expiry = $_POST['card_expiry'] ?? '';
            $card_cvv = $_POST['card_cvv'] ?? '';
            
            if (strlen($card_number) !== 16) {
                $error = t('invalid_card_number');
            } elseif (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
                $error = t('invalid_expiry');
            } elseif (!preg_match('/^\d{3,4}$/', $card_cvv)) {
                $error = t('invalid_cvv');
            }
        } elseif ($posted_payment_method === 'paypal') {
            $paypal_email = $_POST['paypal_email'] ?? '';
            if (!filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
                $error = t('invalid_email');
            }
        } elseif ($posted_payment_method === 'qiwi') {
            $qiwi_phone = preg_replace('/\D/', '', $_POST['qiwi_phone'] ?? '');
            if (!preg_match('/^\+?\d{10,12}$/', $qiwi_phone)) {
                $error = t('invalid_phone');
            }
        } else {
            $error = t('invalid_payment_method');
        }
        
        if (!$error) {
            $conn->begin_transaction();
            try {
                $transaction_id = 'TXN-' . time() . '-' . rand(1000, 9999);
                $stmt = $conn->prepare("UPDATE payments SET status = 'completed', transaction_id = ? WHERE order_id = ?");
                $stmt->bind_param("si", $transaction_id, $order_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("INSERT INTO activation_keys (order_id, game_id, activation_key, encrypted_key, created_at) VALUES (?, ?, ?, ?, NOW())");
                foreach ($order_items as $item) {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $key = generateActivationKey();
                        $stmt->bind_param("iiss", $order_id, $item['game_id'], $key['plain'], $key['encrypted']);
                        $stmt->execute();
                    }
                }
                
                $keys = getActivationKeys($order_id);
                $message = t('order_confirmation') . " #$order_id\n";
                foreach ($keys as $key) {
                    $message .= t('game') . ": " . htmlspecialchars($key['title']) . "\n";
                    $message .= t('key') . ": " . htmlspecialchars($key['activation_key']) . "\n";
                }
                addNotification($user_id, t('order_completed'), $message);
                
                $conn->commit();
                unset($_SESSION['pending_order_id']);
                unset($_SESSION['pending_payment_method']);
                
                header("Location: /payment_success.php?order_id=$order_id");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $error = t('payment_error') . ": " . $e->getMessage();
                error_log("Payment processing error: " . $e->getMessage());
            }
        }
    }
}

require_once 'includes/header.php';
?>

<section class="payment">
    <h2><?= t('payment_for_order') ?> #<?= $order['id'] ?></h2>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="payment-container">
        <div class="payment-methods">
            <h3><?= t('payment_details') ?></h3>
            <p><?= t('payment_method') ?>: 
                <?php
                switch ($payment_method) {
                    case 'credit_card':
                        echo t('credit_card');
                        break;
                    case 'paypal':
                        echo t('paypal');
                        break;
                    case 'qiwi':
                        echo t('qiwi');
                        break;
                    default:
                        echo t('unknown');
                }
                ?>
            </p>
            <p><?= t('total_amount') ?>: <?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></p>
            <p><?= t('please_complete_payment') ?>:</p>
            <form method="post" id="payment-form">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="payment_method" value="<?= htmlspecialchars($payment_method) ?>">
                <?php if ($payment_method === 'credit_card'): ?>
                    <div class="form-group">
                        <label for="card_number"><?= t('card_number') ?>:</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    <div class="form-group">
                        <label for="card_expiry"><?= t('expiry_date') ?>:</label>
                        <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label for="card_cvv"><?= t('cvv') ?>:</label>
                        <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4" required>
                    </div>
                <?php elseif ($payment_method === 'paypal'): ?>
                    <div class="form-group">
                        <label for="paypal_email">PayPal <?= t('email') ?>:</label>
                        <input type="email" id="paypal_email" name="paypal_email" placeholder="example@paypal.com" required>
                    </div>
                <?php elseif ($payment_method === 'qiwi'): ?>
                    <div class="form-group">
                        <label for="qiwi_phone">Qiwi <?= t('phone') ?>:</label>
                        <input type="text" id="qiwi_phone" name="qiwi_phone" placeholder="+7XXXXXXXXXX" maxlength="12" required>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn"><?= t('pay_now') ?></button>
            </form>
        </div>
        
        <div class="order-summary">
            <h3><?= t('order_summary') ?></h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= t('game') ?></th>
                        <th><?= t('quantity') ?></th>
                        <th><?= t('price') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 2) ?> <?= t('currency') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2"><strong><?= t('total') ?>:</strong></td>
                        <td><strong><?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cardNumber = document.getElementById('card_number');
    const cardExpiry = document.getElementById('card_expiry');
    const cardCvv = document.getElementById('card_cvv');

    if (cardNumber) {
        cardNumber.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 16) value = value.slice(0, 16);
            e.target.value = value.replace(/(\d{4})/g, '$1 ').trim();
        });
    }

    if (cardExpiry) {
        cardExpiry.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) value = value.slice(0, 4);
            if (value.length > 2) {
                e.target.value = value.slice(0, 2) + '/' + value.slice(2);
            } else {
                e.target.value = value;
            }
        });
    }

    if (cardCvv) {
        cardCvv.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>