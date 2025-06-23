<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

// Handle error/success messages
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Pagination and search
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Get orders with pagination and search
$orders_data = getOrdersWithPagination($page, $per_page, $search, $status);
$orders = $orders_data['data'];
$total_pages = $orders_data['pages'];

// Build pagination URL
$pagination_url = "orders.php?search=" . urlencode($search) . ($status ? "&status=$status" : '');

require_once '../includes/header.php';
?>

<section class="admin-orders">
    <h2><?= t('manage_orders') ?></h2>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="admin-filters">
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="<?= t('search_orders') ?>" value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value=""><?= t('all_statuses') ?></option>
                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>><?= t('pending') ?></option>
                <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>><?= t('completed') ?></option>
                <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>><?= t('failed') ?></option>
            </select>
            <button type="submit" class="btn"><?= t('apply') ?></button>
            <?php if (!empty($search) || !empty($status)): ?>
                <a href="orders.php" class="btn btn-outline"><?= t('reset') ?></a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= t('user') ?></th>
                        <th><?= t('date') ?></th>
                        <th><?= t('amount') ?></th>
                        <th><?= t('status') ?></th>
                        <th><?= t('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['user_name']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($order['payment_status'] ?? 'none') ?>">
                                    <?= t($order['payment_status'] ?? 'no_payment') ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm"><?= t('details') ?></a>
                                <form method="post" action="update_order_status.php" class="status-form">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?= ($order['payment_status'] ?? '') == 'pending' ? 'selected' : '' ?>><?= t('pending') ?></option>
                                        <option value="completed" <?= ($order['payment_status'] ?? '') == 'completed' ? 'selected' : '' ?>><?= t('completed') ?></option>
                                        <option value="failed" <?= ($order['payment_status'] ?? '') == 'failed' ? 'selected' : '' ?>><?= t('failed') ?></option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-update"><?= t('update') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <?= generatePagination($page, $total_pages, $pagination_url) ?>
                <div class="pagination-info">
                    <?= t('page') ?> <?= $page ?> <?= t('of') ?> <?= $total_pages ?> | <?= t('total_orders') ?>: <?= $orders_data['total'] ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <p><?= t('no_orders_found') ?></p>
            <?php if (!empty($search) || !empty($status)): ?>
                <p><?= t('try_different_search') ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<style>
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 10px; border: 1px solid #ddd; }
.status-badge { padding: 5px 10px; border-radius: 4px; color: white; }
.status-pending { background: #ffc107; }
.status-completed { background: #28a745; }
.status-failed { background: #dc3545; }
.status-none { background: #6c757d; }
.admin-filters { margin-bottom: 20px; }
.search-form { display: flex; gap: 10px; }
.status-form { display: flex; gap: 5px; align-items: center; }
.status-select { padding: 5px; }
.btn-update { padding: 5px 10px; }
.error { color: red; padding: 10px; background: #ffe6e6; border-radius: 4px; margin-bottom: 15px; }
.success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; margin-bottom: 15px; }
</style>

<?php
require_once '../includes/footer.php';
?>