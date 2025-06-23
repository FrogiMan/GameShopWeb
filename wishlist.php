<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$wishlistItems = getWishlistItems($user_id);
?>

<section class="wishlist">
    <h2><?= t('wishlist') ?></h2>
    
    <?php if (count($wishlistItems) > 0): ?>
        <div class="games-grid">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="game-card">
                    <img src="/assets/images/games/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <p class="price"><?= htmlspecialchars($item['price']) ?> <?= t('currency') ?></p>
                    <div class="game-actions">
                        <a href="/game.php?id=<?= $item['game_id'] ?>" class="btn"><?= t('details') ?></a>
                        <form method="post" action="/wishlist_action.php">
                            <input type="hidden" name="game_id" value="<?= $item['game_id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <button type="submit" name="remove_from_wishlist" class="btn btn-outline"><?= t('remove_from_wishlist') ?></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p><?= t('empty_wishlist') ?> <a href="/catalog.php"><?= t('go_to_catalog') ?></a></p>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';
?>