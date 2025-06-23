<?php
ob_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if (isAdmin($_SESSION['user_id'])) {
    header("Location: /admin/");
    exit;
}

$user_id = $_SESSION['user_id'];
$cartItems = getCartItems($user_id);

if (count($cartItems) == 0) {
    header("Location: /cart.php");
    exit;
}

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = t('csrf_validation_failed');
    } else {
        $payment_method = $_POST['payment_method'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = t('invalid_email');
        } elseif (empty($name)) {
            $error = t('all_fields_required');
        } elseif (!in_array($payment_method, ['credit_card', 'paypal', 'qiwi'])) {
            $error = t('invalid_payment_method');
        } else {
            $conn->begin_transaction();
            try {
                $order_id = createOrder($user_id, $total);
                
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, game_id, quantity, price)
                                       VALUES (?, ?, ?, ?)");
                if ($stmt === false) {
                    throw new Exception("Failed to prepare order items statement: " . $conn->error);
                }
                
                foreach ($cartItems as $item) {
                    $stmt->bind_param("iiid", $order_id, $item['game_id'], $item['quantity'], $item['price']);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to insert order item: " . $stmt->error);
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, status, created_at)
                                       VALUES (?, ?, ?, 'pending', NOW())");
                $stmt->bind_param("ids", $order_id, $total, $payment_method);
                $stmt->execute();
                
                clearCart($user_id);
                $conn->commit();
                
                $_SESSION['pending_order_id'] = $order_id;
                $_SESSION['pending_payment_method'] = $payment_method;
                
                header("Location: /payment.php");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $error = t('order_error') . ": " . $e->getMessage();
                error_log("Order processing error: " . $e->getMessage());
            }
        }
    }
}

require_once 'includes/header.php';
?>

<section class="checkout">
    <h2><?= t('checkout') ?></h2>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <div class="order-summary">
            <h3><?= t('order_summary') ?></h3>
            <ul>
                <?php foreach ($cartItems as $item): ?>
                    <li>
                        <?= htmlspecialchars($item['title']) ?> × <?= $item['quantity'] ?>
                        <span><?= number_format($item['price'] * $item['quantity'], 2) ?> <?= t('currency') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total">
                <strong><?= t('total') ?>:</strong>
                <span><?= number_format($total, 2) ?> <?= t('currency') ?></span>
            </div>
        </div>
        
        <div class="checkout-form">
            <h3><?= t('order_details') ?></h3>
            <form method="post" id="checkout-form">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <div class="form-group">
                    <label for="name"><?= t('name') ?>:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email"><?= t('email') ?>:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="payment"><?= t('payment_method') ?>:</label>
                    <select id="payment" name="payment_method" required>
                        <option value="credit_card"><?= t('credit_card') ?></option>
                        <option value="paypal"><?= t('paypal') ?></option>
                        <option value="qiwi"><?= t('qiwi') ?></option>
                    </select>
                </div>
                <button type="submit" name="place_order" class="btn"><?= t('proceed_to_payment') ?></button>
            </form>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>