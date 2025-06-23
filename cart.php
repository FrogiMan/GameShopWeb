<?php
// cart.php

// Начинаем сессию и проверяем авторизацию ДО любого вывода
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Добавляем проверку на админа
require_once 'includes/functions.php';
if (isAdmin($_SESSION['user_id'])) {
    header("Location: /admin/");
    exit;
}

require_once 'includes/functions.php';
require_once 'includes/database.php';

$user_id = $_SESSION['user_id'];
$cartItems = getCartItems($user_id);

// Обработка действий с корзиной ДО любого вывода
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $game_id = (int)$_GET['remove'];
    removeFromCart($user_id, $game_id);
    header("Location: /cart.php");
    exit;
}

if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $game_id => $quantity) {
        $game_id = (int)$game_id;
        $quantity = (int)$quantity;
        
        if ($quantity > 0) {
            $sql = "UPDATE cart_items SET quantity = $quantity 
                    WHERE user_id = $user_id AND game_id = $game_id";
            mysqli_query($conn, $sql);
        } else {
            removeFromCart($user_id, $game_id);
        }
    }
    header("Location: /cart.php");
    exit;
}

// Только после всех возможных редиректов подключаем header
require_once 'includes/header.php';

// Расчет общей суммы
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<section class="cart">
    <h2>Ваша корзина</h2>
    
    <?php if (count($cartItems) > 0): ?>
        <form method="post">
            <table>
                <thead>
                    <tr>
                        <th>Игра</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <img src="/assets/images/games/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" width="50">
                                <?= htmlspecialchars($item['title']) ?>
                            </td>
                            <td><?= htmlspecialchars($item['price']) ?> руб.</td>
                            <td>
                                <input type="number" name="quantity[<?= $item['game_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="10">
                            </td>
                            <td><?= $item['price'] * $item['quantity'] ?> руб.</td>
                            <td>
                                <a href="/cart.php?remove=<?= $item['game_id'] ?>" class="btn">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Итого:</strong></td>
                        <td colspan="2"><strong><?= $total ?> руб.</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn">Обновить корзину</button>
                <a href="/checkout.php" class="btn">Оформить заказ</a>
            </div>
        </form>
    <?php else: ?>
        <p>Ваша корзина пуста. <a href="/catalog.php">Перейти в каталог</a></p>
    <?php endif; ?>
</section>

<script src="/assets/js/script.js"></script>
<script>
// Таймер очистки корзины через 1 час
document.addEventListener('DOMContentLoaded', function() {
    const cartTimer = localStorage.getItem('cartTimer');
    const CART_EXPIRE_TIME = 60 * 60 * 1000; // 1 час
    
    if (!cartTimer) {
        localStorage.setItem('cartTimer', Date.now());
    } else if (Date.now() - cartTimer > CART_EXPIRE_TIME) {
        // Корзина устарела - очищаем
        fetch('/clear_cart.php', { method: 'POST' })
            .then(() => {
                localStorage.removeItem('cartTimer');
                window.location.reload();
            });
    }
    
    // Обновляем таймер при любом действии с корзиной
    document.querySelector('form').addEventListener('submit', function() {
        localStorage.setItem('cartTimer', Date.now());
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>