<?php
// search.php
require_once 'includes/header.php';
require_once 'includes/functions.php';

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    header("Location: /catalog.php");
    exit;
}

$query = trim($_GET['q']);
$games = searchGames($query);
?>

<section class="search-results">
    <h2>Результаты поиска: "<?= htmlspecialchars($query) ?>"</h2>
    
    <?php if (count($games) > 0): ?>
        <div class="games-grid">
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <img src="/assets/images/games/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                    <h3><?= htmlspecialchars($game['title']) ?></h3>
                    <p><?= htmlspecialchars($game['genre']) ?> | <?= htmlspecialchars($game['platform']) ?></p>
                    <p class="price"><?= htmlspecialchars($game['price']) ?> руб.</p>
                    <a href="/game.php?id=<?= $game['id'] ?>" class="btn">Подробнее</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>По вашему запросу ничего не найдено. Попробуйте изменить параметры поиска.</p>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';
?>