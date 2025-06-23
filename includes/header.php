<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

// Prevent direct access to header.php
if (!defined('IN_SCRIPT')) {
    define('IN_SCRIPT', true);
}

// Check admin access for admin pages
$is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
if ($is_admin_page && !(isset($_SESSION['is_admin']) && $_SESSION['is_admin'])) {
    header("Location: /login.php");
    exit;
}

// Установка языка
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
    $_SESSION['preferred_language'] = $_GET['lang'];
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE users SET preferred_language = ? WHERE id = ?");
        $stmt->bind_param("si", $_GET['lang'], $_SESSION['user_id']);
        $stmt->execute();
    }
}

// Установка темы
if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'])) {
    $_SESSION['theme_preference'] = $_GET['theme'];
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
        $stmt->bind_param("si", $_GET['theme'], $_SESSION['user_id']);
        $stmt->execute();
    }
}

$theme = $_SESSION['theme_preference'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['preferred_language'] ?? 'ru' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameStore - <?= t('title') ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?= $theme ?>">
    <header>
        <div class="container">
            <div class="logo">
                <h1><a href="/">GameStore</a></h1>
            </div>
            <nav>
                <ul>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="/admin/"><?= t('admin_panel') ?></a></li>
                        <li><a href="/admin/games.php"><?= t('games') ?></a></li>
                        <li><a href="/admin/orders.php"><?= t('orders') ?></a></li>
                        <li><a href="/admin/users.php"><?= t('users') ?></a></li>
                        <li><a href="/admin/reviews.php"><?= t('reviews') ?></a></li>
                        <li><a href="/admin/analytics.php"><?= t('analytics') ?></a></li>
                    <?php else: ?>
                        <li><a href="/"><?= t('home') ?></a></li>
                        <li><a href="/catalog.php"><?= t('catalog') ?></a></li>
                        <li><a href="/about.php"><?= t('about') ?></a></li>
                        <li><a href="/contact.php"><?= t('contact') ?></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-actions">
                <div class="language-selector">
                    <i class="fas fa-globe"></i>
                    <select onchange="window.location.href='?lang='+this.value">
                        <option value="ru" <?= ($_SESSION['preferred_language'] ?? 'ru') == 'ru' ? 'selected' : '' ?>>Русский</option>
                        <option value="en" <?= ($_SESSION['preferred_language'] ?? 'ru') == 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
                <div class="theme-selector">
                    <i class="<?= $theme == 'dark' ? 'fas fa-moon' : 'fas fa-sun' ?>"></i>
                    <select onchange="window.location.href='?theme='+this.value">
                        <option value="light" <?= ($theme == 'light') ? 'selected' : '' ?>><?= t('light_theme') ?></option>
                        <option value="dark" <?= ($theme == 'dark') ? 'selected' : '' ?>><?= t('dark_theme') ?></option>
                    </select>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span><?= t('welcome') ?>, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="/profile.php"><?= t('profile') ?></a>
                    <?php if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']): ?>
                        <a href="/cart.php"><?= t('cart') ?></a>
                        <a href="/wishlist.php"><?= t('wishlist') ?></a>
                    <?php endif; ?>
                    <a href="/logout.php"><?= t('logout') ?></a>
                <?php else: ?>
                    <a href="/login.php"><?= t('login') ?></a>
                    <a href="/register.php"><?= t('register') ?></a>
                <?php endif; ?>
            </div>
        </div>
        <input type="hidden" id="csrf_token" value="<?= generateCSRFToken() ?>">
    </header>
    <main>