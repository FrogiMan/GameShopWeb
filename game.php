<?php
ob_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /catalog.php");
    exit;
}

$game_id = (int)$_GET['id'];
$game = getGameById($game_id);

if (!$game) {
    header("Location: /catalog.php");
    exit;
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($quantity < 1 || $quantity > 10) {
        $error_message = t('invalid_quantity');
    } elseif (addToCart($_SESSION['user_id'], $game_id, $quantity)) {
        addNotification($_SESSION['user_id'], t('cart_updated'), t('game_added_to_cart', ['title' => $game['title']]));
        header("Location: /cart.php");
        exit;
    } else {
        $error_message = t('cart_add_error');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review']) && isset($_SESSION['user_id'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating < 1 || $rating > 10) {
        $error_message = t('invalid_rating');
    } elseif (empty($comment)) {
        $error_message = t('comment_required');
    } elseif (addReview($_SESSION['user_id'], $game_id, $rating, $comment)) {
        addNotification($_SESSION['user_id'], t('new_review'), t('review_added'));
        $success_message = t('review_added');
    } else {
        $error_message = t('review_add_error');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_review']) && isset($_SESSION['user_id'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $review_id = (int)$_POST['review_id'];
    $reason = trim($_POST['reason']);
    
    if (empty($reason)) {
        $error_message = t('reason_required');
    } else {
        if (addReviewComplaint($_SESSION['user_id'], $review_id, $reason)) {
            $success_message = t('complaint_submitted');
        } else {
            $error_message = t('cannot_complain_own_review');
        }
    }
}

$reviews = getGameReviews($game_id);
require_once 'includes/header.php';
?>

<section class="game-details">
    <div class="game-info">
        <img src="/assets/images/games/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['title']) ?>">
        <div class="game-description">
            <h1><?= htmlspecialchars($game['title']) ?></h1>
            <p><strong><?= t('genre') ?>:</strong> <?= htmlspecialchars($game['genre']) ?></p>
            <p><strong><?= t('platform') ?>:</strong> <?= htmlspecialchars($game['platform']) ?></p>
            <p><strong><?= t('release_date') ?>:</strong> <?= date('d.m.Y', strtotime($game['release_date'])) ?></p>
            <p><strong><?= t('rating') ?>:</strong> <?= htmlspecialchars($game['rating']) ?>/10</p>
            <p class="price"><?= htmlspecialchars($game['price']) ?> <?= t('currency') ?></p>
            
            <?php if ($success_message): ?>
                <div class="success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="game-actions">
                    <form method="post">
                        <input type="number" name="quantity" value="1" min="1" max="10">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <button type="submit" name="add_to_cart" class="btn"><?= t('add_to_cart') ?></button>
                    </form>
                    <form method="post" action="/wishlist_action.php">
                        <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <button type="submit" name="<?= isGameInWishlist($_SESSION['user_id'], $game['id']) ? 'remove_from_wishlist' : 'add_to_wishlist' ?>" class="btn btn-outline">
                            <?= isGameInWishlist($_SESSION['user_id'], $game['id']) ? t('remove_from_wishlist') : t('add_to_wishlist') ?>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <p><?= t('login_to_add_to_cart') ?> <a href="/login.php"><?= t('login') ?></a> <?= t('or') ?> <a href="/register.php"><?= t('register') ?></a>.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="game-description-full">
        <h2><?= t('description') ?></h2>
        <p><?= nl2br(htmlspecialchars($game['description'])) ?></p>
    </div>
</section>

<section class="game-reviews">
    <h2><?= t('reviews') ?></h2>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="add-review">
            <h3><?= t('add_review') ?></h3>
            <form method="post">
                <div class="rating">
                    <label><?= t('rating') ?>:</label>
                    <select name="rating" required>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == 5 ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <textarea name="comment" placeholder="<?= t('your_review') ?>" required></textarea>
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <button type="submit" name="add_review" class="btn"><?= t('submit_review') ?></button>
            </form>
        </div>
    <?php endif; ?>
    
    <div class="reviews-list">
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <h4><?= htmlspecialchars($review['user_name']) ?></h4>
                        <div class="rating"><?= t('rating') ?>: <?= htmlspecialchars($review['rating']) ?>/10</div>
                        <div class="date"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></div>
                        <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] != $review['user_id'] || isAdmin($_SESSION['user_id']))): ?>
                            <button class="report-review-btn" data-review-id="<?= $review['id'] ?>"><?= t('report_user') ?></button>
                        <?php endif; ?>
                    </div>
                    <div class="review-content">
                        <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    </div>
                </div>
                <div class="report-form" id="report-form-<?= $review['id'] ?>" style="display: none;">
                    <form method="post">
                        <textarea name="reason" placeholder="<?= t('report_reason') ?>" required></textarea>
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <button type="submit" name="report_review" class="btn"><?= t('submit_complaint') ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?= t('no_reviews') ?></p>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.report-review-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const reviewId = btn.dataset.reviewId;
            const form = document.getElementById(`report-form-${reviewId}`);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>