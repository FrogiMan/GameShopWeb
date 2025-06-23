<?php
// index.php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Получаем популярные игры (первые 6)
$popularGames = getGames(6);
// Получаем новинки (сортировка по дате выхода)
$newReleases = getGames(6, null, null, 'release_date DESC');
?>

<div class="hero">
    <h2>Добро пожаловать в GameStore!</h2>
    <p>Лучшие игры по лучшим ценам</p>
    <a href="/catalog.php" class="btn">Перейти в каталог</a>
</div>

<section class="popular-games">
    <h2>Популярные игры</h2>
    <div class="games-grid">
        <?php foreach ($popularGames as $game): ?>
            <div class="game-card fade-in delay-1">
                <img src="/assets/images/games/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                <h3><?= htmlspecialchars($game['title']) ?></h3>
                <p><?= htmlspecialchars($game['genre']) ?> | <?= htmlspecialchars($game['platform']) ?></p>
                <p class="price"><?= htmlspecialchars($game['price']) ?> руб.</p>
                <a href="/game.php?id=<?= $game['id'] ?>" class="btn">Подробнее</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="new-releases">
    <h2>Новинки</h2>
    <div class="games-grid">
        <?php foreach ($newReleases as $game): ?>
            <div class="game-card fade-in delay-1">
                <img src="/assets/images/games/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                <h3><?= htmlspecialchars($game['title']) ?></h3>
                <p><?= htmlspecialchars($game['genre']) ?> | <?= htmlspecialchars($game['platform']) ?></p>
                <p class="price"><?= htmlspecialchars($game['price']) ?> руб.</p>
                <a href="/game.php?id=<?= $game['id'] ?>" class="btn">Подробнее</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>