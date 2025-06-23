<?php
// clear_cart.php
require_once 'includes/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM cart_items WHERE user_id = $user_id";
    mysqli_query($conn, $sql);
    
    echo json_encode(['success' => true]);
} else {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false]);
}
?>