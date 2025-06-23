<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Получаем параметры фильтрации
$genre = isset($_GET['genre']) ? $_GET['genre'] : null;
$platform = isset($_GET['platform']) ? $_GET['platform'] : null;
$price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : null;

$order = null;
switch ($sort) {
    case 'price_asc': $order = 'price ASC'; break;
    case 'price_desc': $order = 'price DESC'; break;
    case 'rating': $order = 'rating DESC'; break;
    case 'newest': $order = 'release_date DESC'; break;
}

// Получаем все игры с учетом фильтров
$games = getGames(null, $genre, $platform, $order, $price_min, $price_max);

// Получаем уникальные жанры и платформы для фильтров
$genres = [];
$platforms = [];
$allGames = getGames();
foreach ($allGames as $game) {
    if (!in_array($game['genre'], $genres)) {
        $genres[] = $game['genre'];
    }
    if (!in_array($game['platform'], $platforms)) {
        $platforms[] = $game['platform'];
    }
}

// Получаем рекомендации для авторизованного пользователя
$recommendedGames = isset($_SESSION['user_id']) ? getRecommendedGames($_SESSION['user_id']) : [];
?>

<section class="catalog">
    <h2><?= t('catalog') ?></h2>
    
    <div class="filters">
        <form method="get" action="/catalog.php">
            <div class="filter-group">
                <label for="genre"><?= t('genre') ?>:</label>
                <select name="genre" id="genre">
                    <option value=""><?= t('all_genres') ?></option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>" <?= $genre == $g ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="platform"><?= t('platform') ?>:</label>
                <select name="platform" id="platform">
                    <option value=""><?= t('all_platforms') ?></option>
                    <?php foreach ($platforms as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= $platform == $p ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="price_min"><?= t('price_from') ?>:</label>
                <input type="number" name="price_min" id="price_min" step="0.01" min="0" value="<?= htmlspecialchars($price_min ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label for="price_max"><?= t('price_to') ?>:</label>
                <input type="number" name="price_max" id="price_max" step="0.01" min="0" value="<?= htmlspecialchars($price_max ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <label for="sort"><?= t('sort_by') ?>:</label>
                <select name="sort" id="sort">
                    <option value=""><?= t('default') ?></option>
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>><?= t('price_asc') ?></option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>><?= t('price_desc') ?></option>
                    <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>><?= t('rating') ?></option>
                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>><?= t('newest') ?></option>
                </select>
            </div>
            
            <button type="submit" class="btn"><?= t('apply_filters') ?></button>
            <a href="/catalog.php" class="btn btn-outline"><?= t('reset') ?></a>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        </form>
    </div>
    
    <div class="games-grid">
        <?php if (count($games) > 0): ?>
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <img src="/assets/images/games/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                    <h3><?= htmlspecialchars($game['title']) ?></h3>
                    <p><?= htmlspecialchars($game['genre']) ?> | <?= htmlspecialchars($game['platform']) ?></p>
                    <p class="price"><?= htmlspecialchars($game['price']) ?> <?= t('currency') ?></p>
                    <div class="game-actions">
                        <a href="/game.php?id=<?= $game['id'] ?>" class="btn"><?= t('details') ?></a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="post" action="/wishlist_action.php">
                                <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <button type="submit" name="<?= isGameInWishlist($_SESSION['user_id'], $game['id']) ? 'remove_from_wishlist' : 'add_to_wishlist' ?>" class="btn btn-outline">
                                    <?= isGameInWishlist($_SESSION['user_id'], $game['id']) ? t('remove_from_wishlist') : t('add_to_wishlist') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?= t('no_games_found') ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <section class="recommended-games">
            <h2><?= t('recommended_games') ?></h2>
            <?php if (count($recommendedGames) > 0): ?>
                <div class="games-grid">
                    <?php foreach ($recommendedGames as $game): ?>
                        <div class="game-card">
                            <img src="/assets/images/games/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                            <h3><?= htmlspecialchars($game['title']) ?></h3>
                            <p><?= htmlspecialchars($game['genre']) ?> | <?= htmlspecialchars($game['platform']) ?></p>
                            <p class="price"><?= htmlspecialchars($game['price']) ?> <?= t('currency') ?></p>
                            <div class="game-actions">
                                <a href="/game.php?id=<?= $game['id'] ?>" class="btn"><?= t('details') ?></a>
                                <form method="post" action="/wishlist_action.php">
                                    <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <button type="submit" name="<?= isGameInWishlist($_SESSION['user_id'], $game['id']) ? 'remove_from_wishlist' : 'add_to_wishlist' ?>" class="btn btn-outline">
                                        <?= isGameInWishlist($_SESSION['user_id'], $game['id']) ? t('remove_from_wishlist') : t('add_to_wishlist') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><?= t('no_recommendations') ?> <a href="/catalog.php"><?= t('go_to_catalog') ?></a></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';
?>