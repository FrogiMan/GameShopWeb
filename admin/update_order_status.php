<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header("Location: orders.php?error=" . urlencode(t('unauthorized_access')));
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header("Location: orders.php?error=" . urlencode(t('csrf_validation_failed')));
    exit;
}

$order_id = (int)$_POST['order_id'];
$status = $_POST['status'] ?? 'pending';

// Update order status using the new function
if (updateOrderStatus($order_id, $status)) {
    logAdminAction($_SESSION['user_id'], 'update_order_status', "Order ID: $order_id, New Status: $status");
    header("Location: orders.php?success=" . urlencode(t('order_status_updated')));
} else {
    header("Location: orders.php?error=" . urlencode(t('order_status_update_failed')));
}
exit;
?>