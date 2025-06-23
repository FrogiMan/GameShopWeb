<?php
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $user_id = $_SESSION['user_id'];
    $game_id = (int)$_POST['game_id'];
    
    if (isset($_POST['add_to_wishlist'])) {
        if (addToWishlist($user_id, $game_id)) {
            // Use the now-defined sendPushNotification function
            sendPushNotification($user_id, t('wishlist'), t('added_to_wishlist'));
        }
    } elseif (isset($_POST['remove_from_wishlist'])) {
        removeFromWishlist($user_id, $game_id);
    }
    
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/wishlist.php'));
    exit;
}
?>