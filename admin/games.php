<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

// Обработка удаления игры
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['csrf_token']) && verifyCSRFToken($_GET['csrf_token'])) {
    rateLimit();
    try {
        $game_id = (int)$_GET['delete'];
        if (deleteGame($game_id)) {
            logAdminAction($_SESSION['user_id'], 'delete_game', "Deleted game ID: $game_id");
            header("Location: games.php?success=Игра успешно удалена");
        } else {
            header("Location: games.php?error=Ошибка удаления игры");
        }
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: games.php?error=Ошибка удаления игры");
        exit;
    }
}

// Пагинация и поиск
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$search = isset($_GET['search']) ? trim(filter_var($_GET['search'], FILTER_SANITIZE_STRING)) : '';

$games_data = getGamesWithPagination($page, $per_page, $search);
$games = $games_data['data'];
$total_pages = $games_data['pages'];

require_once '../includes/header.php';
?>

<section class="admin-games">
    <h2><?= t('manage_games') ?></h2>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    
    <div class="admin-actions">
        <a href="game_edit.php" class="btn"><?= t('add_game') ?></a>
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="<?= t('search_games') ?>" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn"><?= t('search') ?></button>
            <?php if (!empty($search)): ?>
                <a href="games.php" class="btn btn-outline"><?= t('reset') ?></a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (!empty($games)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= t('title') ?></th>
                        <th><?= t('genre') ?></th>
                        <th><?= t('platform') ?></th>
                        <th><?= t('price') ?></th>
                        <th><?= t('rating') ?></th>
                        <th><?= t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?= $game['id'] ?></td>
                            <td>
                                <img src="/assets/images/games/<?= htmlspecialchars($game['image']) ?>" width="50" alt="<?= htmlspecialchars($game['title']) ?>">
                                <?= htmlspecialchars($game['title']) ?>
                            </td>
                            <td><?= htmlspecialchars($game['genre']) ?></td>
                            <td><?= htmlspecialchars($game['platform']) ?></td>
                            <td><?= number_format($game['price'], 2) ?> <?= t('currency') ?></td>
                            <td><?= $game['rating'] ?>/10</td>
                            <td class="actions">
                                <a href="game_edit.php?id=<?= $game['id'] ?>" class="btn btn-sm"><?= t('edit') ?></a>
                                <a href="games.php?delete=<?= $game['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('<?= t('confirm_delete_game') ?>')"><?= t('delete') ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1&search=<?= urlencode($search) ?>">«</a>
                        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">‹</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">›</a>
                        <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>">»</a>
                    <?php endif; ?>
                </div>
                <div class="pagination-info">
                    <?= t('page') ?> <?= $page ?> <?= t('of') ?> <?= $total_pages ?> | <?= t('total_games') ?>: <?= $games_data['total'] ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <p><?= t('no_games_found') ?></p>
            <?php if (!empty($search)): ?>
                <p><?= t('try_different_search') ?></p>
            <?php else: ?>
                <p><?= t('add_first_game') ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<style>
.error { color: red; padding: 10px; background: #ffe6e6; border-radius: 4px; margin-bottom: 15px; }
.success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; margin-bottom: 15px; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 10px; border: 1px solid #ddd; }
.btn-sm { padding: 5px 10px; font-size: 14px; }
.btn-danger { background: #dc3545; color: white; }
</style>

<?php
require_once '../includes/footer.php';
?>