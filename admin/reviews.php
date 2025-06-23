<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

// Handle review deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    rateLimit();
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    $review_id = (int)$_GET['delete'];
    deleteReview($review_id);
    logAdminAction($_SESSION['user_id'], 'delete_review', "Deleted review ID: $review_id");
    header("Location: reviews.php");
    exit;
}

// Handle complaint status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_complaint'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    $complaint_id = (int)$_POST['complaint_id'];
    $status = $_POST['status'];
    updateReviewComplaintStatus($complaint_id, $status);
    logAdminAction($_SESSION['user_id'], 'update_complaint', "Updated complaint ID: $complaint_id to status: $status");
    header("Location: reviews.php");
    exit;
}

// Get all reviews
$stmt = $conn->prepare("SELECT r.*, u.name as user_name, g.title as game_title 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        JOIN games g ON r.game_id = g.id 
                        ORDER BY r.created_at DESC");
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get review complaints
$complaints = getReviewComplaints();

require_once '../includes/header.php';
?>

<section class="admin-reviews">
    <h2><?= t('manage_reviews') ?></h2>
    
    <div class="tabs">
        <button class="tab-button active" onclick="showTab('reviews')"><?= t('all_reviews') ?></button>
        <button class="tab-button" onclick="showTab('complaints')"><?= t('review_complaints') ?></button>
    </div>
    
    <div id="reviews" class="tab-content active">
        <h3><?= t('all_reviews') ?></h3>
        <?php if ($reviews): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= t('game') ?></th>
                        <th><?= t('user') ?></th>
                        <th><?= t('rating') ?></th>
                        <th><?= t('comment') ?></th>
                        <th><?= t('date') ?></th>
                        <th><?= t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?= $review['id'] ?></td>
                            <td><?= htmlspecialchars($review['game_title']) ?></td>
                            <td><?= htmlspecialchars($review['user_name']) ?></td>
                            <td><?= $review['rating'] ?>/10</td>
                            <td><?= htmlspecialchars(substr($review['comment'], 0, 100)) . (strlen($review['comment']) > 100 ? '...' : '') ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                            <td>
                                <a href="/game.php?id=<?= $review['game_id'] ?>" class="btn btn-sm"><?= t('view') ?></a>
                                <a href="reviews.php?delete=<?= $review['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('<?= t('confirm_delete') ?>')"><?= t('delete') ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?= t('no_reviews') ?></p>
        <?php endif; ?>
    </div>
    
    <div id="complaints" class="tab-content" style="display: none;">
        <h3><?= t('review_complaints') ?></h3>
        <?php if ($complaints): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= t('game') ?></th>
                        <th><?= t('complainant') ?></th>
                        <th><?= t('review') ?></th>
                        <th><?= t('reason') ?></th>
                        <th><?= t('status') ?></th>
                        <th><?= t('date') ?></th>
                        <th><?= t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?= $complaint['id'] ?></td>
                            <td><?= htmlspecialchars($complaint['game_title']) ?></td>
                            <td><?= htmlspecialchars($complaint['complainant']) ?></td>
                            <td><?= htmlspecialchars(substr($complaint['comment'], 0, 50)) . (strlen($complaint['comment']) > 50 ? '...' : '') ?></td>
                            <td><?= htmlspecialchars(substr($complaint['reason'], 0, 50)) . (strlen($complaint['reason']) > 50 ? '...' : '') ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?= $complaint['status'] == 'pending' ? 'selected' : '' ?>><?= t('pending') ?></option>
                                        <option value="resolved" <?= $complaint['status'] == 'resolved' ? 'selected' : '' ?>><?= t('resolved') ?></option>
                                        <option value="rejected" <?= $complaint['status'] == 'rejected' ? 'selected' : '' ?>><?= t('rejected') ?></option>
                                    </select>
                                    <button type="submit" name="update_complaint" style="display: none;"></button>
                                </form>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($complaint['created_at'])) ?></td>
                            <td>
                                <a href="/game.php?id=<?= $complaint['game_id'] ?>" class="btn btn-sm"><?= t('view') ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?= t('no_complaints') ?></p>
        <?php endif; ?>
    </div>
</section>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
}
</script>

<?php
require_once '../includes/footer.php';
?>