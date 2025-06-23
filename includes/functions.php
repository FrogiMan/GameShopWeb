<?php
require_once 'database.php';

// Основные функции
function t($key, $replacements = []) {
    static $translations = [
        'ru' => [
            'home' => 'Главная',
            'catalog' => 'Каталог',
            'about' => 'О нас',
            'contact' => 'Контакты',
            'login' => 'Войти',
            'register' => 'Регистрация',
            'logout' => 'Выйти',
            'profile' => 'Профиль',
            'cart' => 'Корзина',
            'wishlist' => 'Избранное',
            'add_to_cart' => 'В корзину',
            'remove_from_cart' => 'Удалить',
            'checkout' => 'Оформить заказ',
            'order_history' => 'История заказов',
            'change_password' => 'Сменить пароль',
            'edit_profile' => 'Редактировать профиль',
            'search' => 'Поиск',
            'currency' => 'руб.',
            'admin_panel' => 'Админ-панель',
            'games' => 'Игры',
            'orders' => 'Заказы',
            'reviews' => 'Отзывы',
            'users' => 'Пользователи',
            'analytics' => 'Статистика',
            'your_profile' => 'Ваш профиль',
            'personal_info' => 'Персональная информация',
            'name' => 'Имя',
            'email' => 'Почта',
            'language' => 'Язык',
            'theme' => 'Тема',
            'empty_wishlist' => 'Список желаний пуст.',
            'order_number' => 'Номер заказа',
            'date' => 'Дата',
            'amount' => 'Сумма',
            'actions' => 'Действия',
            'notifications' => 'Уведомления',
            'enable_notifications' => 'Включить уведомления',
            'details' => 'Подробности',
            'save' => 'Сохранить',
            'sales_analytics' => 'Аналитика продаж',
            'start_date' => 'Дата начала',
            'end_date' => 'Дата окончания',
            'filter_by_game' => 'Фильтр по игре',
            'all_games' => 'Все игры',
            'apply_filters' => 'Применить фильтры',
            'reset' => 'Сбросить',
            'total_revenue' => 'Общая выручка',
            'total_sales' => 'Всего продаж',
            'unique_games' => 'Уникальных игр',
            'revenue_by_month' => 'Выручка по месяцам',
            'top_games' => 'Топ игр',
            'detailed_report' => 'Детальный отчет',
            'game' => 'Игра',
            'sold' => 'Продано',
            'revenue' => 'Выручка',
            'avg_price' => 'Средняя цена',
            'manage_games' => 'Управление играми',
            'add_game' => 'Добавить игру',
            'search_games' => 'Поиск игр',
            'no_games_found' => 'Игры не найдены',
            'try_different_search' => 'Попробуйте другой поиск',
            'add_first_game' => 'Добавьте первую игру',
            'title' => 'Название',
            'genre' => 'Жанр',
            'platform' => 'Платформа',
            'price' => 'Цена',
            'rating' => 'Рейтинг',
            'edit' => 'Редактировать',
            'delete' => 'Удалить',
            'confirm_delete_game' => 'Вы уверены, что хотите удалить эту игру?',
            'manage_orders' => 'Управление заказами',
            'search_orders' => 'Поиск заказов',
            'all_statuses' => 'Все статусы',
            'apply' => 'Применить',
            'no_orders_found' => 'Заказы не найдены',
            'total_orders' => 'Всего заказов',
            'page' => 'Страница',
            'of' => 'из',
            'status' => 'Статус',
            'pending' => 'В ожидании',
            'completed' => 'Завершено',
            'failed' => 'Неудачно',
            'no_payment' => 'Нет оплаты',
            'manage_reviews' => 'Управление отзывами',
            'all_reviews' => 'Все отзывы',
            'review_complaints' => 'Жалобы на отзывы',
            'no_reviews' => 'Отзывы отсутствуют',
            'no_complaints' => 'Жалобы отсутствуют',
            'complainant' => 'Жалобщик',
            'reason' => 'Причина',
            'view' => 'Просмотреть',
            'confirm_delete' => 'Вы уверены, что хотите удалить этот отзыв?',
            'manage_users' => 'Управление пользователями',
            'add_user' => 'Добавить пользователя',
            'role' => 'Роль',
            'admin' => 'Администратор',
            'user' => 'Пользователь',
            'confirm_delete_user' => 'Вы уверены, что хотите удалить этого пользователя?',
            'save_changes' => 'Сохранить изменения',
            'leave_blank' => 'Оставить пустым',
            'admin_role' => 'Роль администратора',
            'password' => 'Пароль',
            'new_password' => 'Новый пароль',
            'confirm_password' => 'Подтвердить пароль',
            'invalid_current_password' => 'Неверный текущий пароль',
            'passwords_not_match' => 'Пароли не совпадают',
            'password_too_short' => 'Пароль слишком короткий (минимум 6 символов)',
            'password_updated' => 'Пароль успешно обновлен',
            'profile_updated' => 'Профиль успешно обновлен',
            'profile_update_error' => 'Ошибка обновления профиля',
            'all_fields_required' => 'Все поля обязательны',
            'invalid_email' => 'Неверный формат email',
            'email_exists' => 'Этот email уже используется',
            'month_revenue' => 'Месячная выручка',
            'top_selling_games' => 'Самые продаваемые игры',
            'no_data' => 'Данные отсутствуют',
            'recent_orders' => 'Недавние заказы',
            'no_orders' => 'Заказы отсутствуют.',
            'description' => 'Описание',
            'release_date' => 'Дата выпуска',
            'image_required' => 'Требуется изображение',
            'invalid_file_format' => 'Неверный формат файла',
            'file_too_large' => 'Файл слишком большой',
            'upload_error' => 'Ошибка загрузки файла',
            'save_game_error' => 'Ошибка сохранения игры',
            'edit_game' => 'Редактировать игру',
            'current_image' => 'Текущее изображение',
            'total_games' => 'Всего игр',
            'total_users' => 'Всего пользователей',
            'welcome' => 'Добро пожаловать',
            'manage' => 'Управление',
            'monthly_revenue' => 'Месячная выручка',
            'sales' => 'Продажи',
            'customer' => 'Клиент',
            'order_details' => 'Детали заказа',
            'order_info' => 'Информация о заказе',
            'items' => 'Товары',
            'quantity' => 'Количество',
            'total' => 'Итого',
            'activation_keys' => 'Ключи активации',
            'key' => 'Ключ',
            'no_items' => 'Товары отсутствуют',
            'no_activation_keys' => 'Ключи активации отсутствуют',
            'back_to_orders' => 'Вернуться к заказам',
            'invalid_order_id' => 'Неверный ID заказа',
            'order_not_found' => 'Заказ не найден',
            'go_to_catalog' => 'Перейти к каталогу.',
            'recommended_games' => 'Рекомендуемые игры',
            'no_recommendations' => 'Нет рекомендаций',
            'all_genres' => 'Все жанры',
            'all_platforms' => 'Все платформы',
            'price_from' => 'Цена от',
            'price_to' => 'Цена до',
            'sort_by' => 'Сортировать по',
            'default' => 'По умолчанию',
            'price_asc' => 'Цена: по возрастанию',
            'price_desc' => 'Цена: по убыванию',
            'newest' => 'Новинки',
            'add_to_wishlist' => 'Добавить в избранное',
            'remove_from_wishlist' => 'Удалить из избранного',
            'russian' => 'Русский',
            'english' => 'Английский',
            'light_theme' => 'Светлая тема',
            'dark_theme' => 'Темная тема',
            'order_success' => 'Заказ #{order_id} успешно оформлен!',
            'add_review' => 'Добавить отзыв',
            'your_review' => 'Ваш отзыв',
            'submit_review' => 'Отправить отзыв',
            'report_user' => 'Пожаловаться',
            'report_reason' => 'Причина жалобы',
            'submit_complaint' => 'Отправить жалобу',
            'complaint_submitted' => 'Жалоба отправлена',
            'login_to_add_to_cart' => 'Войдите, чтобы добавить в корзину',
            'or' => 'или',
            'new_review' => 'Новый отзыв',
            'review_added' => 'Отзыв успешно добавлен',
            'cart_updated' => 'Корзина обновлена',
            'game_added_to_cart' => 'Игра "{title}" добавлена в корзину',
            'review_complaint' => 'Жалоба на отзыв',
            'invalid_rating' => 'Неверный рейтинг',
            'comment_required' => 'Комментарий обязателен',
            'review_add_error' => 'Ошибка добавления отзыва',
            'reason_required' => 'Причина жалобы обязательна',
            'complaint_error' => 'Ошибка отправки жалобы',
            'invalid_quantity' => 'Неверное количество',
            'cart_add_error' => 'Ошибка добавления в корзину',
            'new_game_added' => 'Новая игра добавлена',
            'game_added_notification' => 'Игра "{title}" добавлена в каталог',
            'order_completed' => 'Заказ завершен',
            'order_confirmation' => 'Подтверждение заказа',
            'payment_for_order' => 'Оплата заказа',
            'payment_details' => 'Детали оплаты',
            'payment_method' => 'Способ оплаты',
            'total_amount' => 'Общая сумма',
            'credit_card' => 'Кредитная карта',
            'paypal' => 'PayPal',
            'qiwi' => 'Qiwi',
            'pay_now' => 'Оплатить сейчас',
            'pay_with_paypal' => 'Оплатить через PayPal',
            'card_number' => 'Номер карты',
            'expiry_date' => 'Дата окончания',
            'cvv' => 'CVV',
            'invalid_card_number' => 'Неверный номер карты',
            'invalid_expiry' => 'Неверная дата окончания',
            'invalid_cvv' => 'Неверный CVV',
            'payment_error' => 'Ошибка оплаты',
            'order_error' => 'Ошибка оформления заказа',
            'proceed_to_payment' => 'Перейти к оплате',
            'order_summary' => 'Сводка заказа',
            'pay_order' => 'Оплатить заказ',
            'transaction_id' => 'ID транзакции',
            'delete_notification' => 'Удалить уведомление',
            'delete_order' => 'Удалить заказ',
            'cannot_complain_own_review' => 'Нельзя пожаловаться на свой отзыв',
            'notification_deleted' => 'Уведомление удалено',
            'order_deleted' => 'Заказ удален',
            'delete_notification_confirm' => 'Вы уверены, что хотите удалить это уведомление?',
            'delete_order_confirm' => 'Вы уверены, что хотите удалить этот заказ?',
        ],
        'en' => [
            // Английские переводы (оставлены без изменений, для краткости)
            'home' => 'Home',
            'catalog' => 'Catalog',
            'about' => 'About',
            'contact' => 'Contact',
            'login' => 'Login',
            'register' => 'Register',
            'logout' => 'Logout',
            'profile' => 'Profile',
            'cart' => 'Cart',
            'wishlist' => 'Wishlist',
            'add_to_cart' => 'Add to cart',
            'remove_from_cart' => 'Remove',
            'checkout' => 'Checkout',
            'order_history' => 'Order history',
            'change_password' => 'Change password',
            'edit_profile' => 'Edit profile',
            'search' => 'Search',
            'currency' => 'RUB',
            'admin_panel' => 'Admin Panel',
            'games' => 'Games',
            'orders' => 'Orders',
            'reviews' => 'Reviews',
            'users' => 'Users',
            'analytics' => 'Analytics',
            'your_profile' => 'Your Profile',
            'personal_info' => 'Personal Information',
            'name' => 'Name',
            'email' => 'Email',
            'language' => 'Language',
            'theme' => 'Theme',
            'empty_wishlist' => 'Wishlist is empty.',
            'order_number' => 'Order Number',
            'date' => 'Date',
            'amount' => 'Amount',
            'actions' => 'Actions',
            'notifications' => 'Notifications',
            'enable_notifications' => 'Enable Notifications',
            'details' => 'Details',
            'save' => 'Save',
            'sales_analytics' => 'Sales Analytics',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'filter_by_game' => 'Filter by Game',
            'all_games' => 'All Games',
            'apply_filters' => 'Apply Filters',
            'reset' => 'Reset',
            'total_revenue' => 'Total Revenue',
            'total_sales' => 'Total Sales',
            'unique_games' => 'Unique Games',
            'revenue_by_month' => 'Revenue by Month',
            'top_games' => 'Top Games',
            'detailed_report' => 'Detailed Report',
            'game' => 'Game',
            'sold' => 'Sold',
            'revenue' => 'Revenue',
            'avg_price' => 'Average Price',
            'manage_games' => 'Manage Games',
            'add_game' => 'Add Game',
            'search_games' => 'Search Games',
            'no_games_found' => 'No games found',
            'try_different_search' => 'Try a different search',
            'add_first_game' => 'Add your first game',
            'title' => 'Title',
            'genre' => 'Genre',
            'platform' => 'Platform',
            'price' => 'Price',
            'rating' => 'Rating',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'confirm_delete_game' => 'Are you sure you want to delete this game?',
            'manage_orders' => 'Manage Orders',
            'search_orders' => 'Search Orders',
            'all_statuses' => 'All Statuses',
            'apply' => 'Apply',
            'no_orders_found' => 'No orders found',
            'total_orders' => 'Total Orders',
            'page' => 'Page',
            'of' => 'of',
            'status' => 'Status',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'no_payment' => 'No Payment',
            'manage_reviews' => 'Manage Reviews',
            'all_reviews' => 'All Reviews',
            'review_complaints' => 'Review Complaints',
            'no_reviews' => 'No reviews available',
            'no_complaints' => 'No complaints available',
            'complainant' => 'Complainant',
            'reason' => 'Reason',
            'view' => 'View',
            'confirm_delete' => 'Are you sure you want to delete this review?',
            'manage_users' => 'Manage Users',
            'add_user' => 'Add User',
            'role' => 'Role',
            'admin' => 'Admin',
            'user' => 'User',
            'confirm_delete_user' => 'Are you sure you want to delete this user?',
            'save_changes' => 'Save Changes',
            'leave_blank' => 'Leave blank',
            'admin_role' => 'Admin Role',
            'password' => 'Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password',
            'invalid_current_password' => 'Invalid current password',
            'passwords_not_match' => 'Passwords do not match',
            'password_too_short' => 'Password too short (minimum 6 characters)',
            'password_updated' => 'Password updated successfully',
            'profile_updated' => 'Profile updated successfully',
            'profile_update_error' => 'Error updating profile',
            'all_fields_required' => 'All fields are required',
            'invalid_email' => 'Invalid email format',
            'email_exists' => 'This email is already in use',
            'month_revenue' => 'Monthly Revenue',
            'top_selling_games' => 'Top Selling Games',
            'no_data' => 'No data available',
            'recent_orders' => 'Recent Orders',
            'no_orders' => 'No orders available',
            'description' => 'Description',
            'release_date' => 'Release Date',
            'image_required' => 'Image required',
            'invalid_file_format' => 'Invalid file format',
            'file_too_large' => 'File too large',
            'upload_error' => 'Upload error',
            'save_game_error' => 'Error saving game',
            'edit_game' => 'Edit Game',
            'current_image' => 'Current Image',
            'total_games' => 'Total Games',
            'total_users' => 'Total Users',
            'welcome' => 'Welcome',
            'manage' => 'Manage',
            'monthly_revenue' => 'Monthly Revenue',
            'sales' => 'Sales',
            'customer' => 'Customer',
            'order_details' => 'Order Details',
            'order_info' => 'Order Information',
            'items' => 'Items',
            'quantity' => 'Quantity',
            'total' => 'Total',
            'activation_keys' => 'Activation Keys',
            'key' => 'Key',
            'no_items' => 'No items available',
            'no_activation_keys' => 'No activation keys available',
            'back_to_orders' => 'Back to Orders',
            'invalid_order_id' => 'Invalid Order ID',
            'order_not_found' => 'Order Not Found',
            'go_to_catalog' => 'Go to catalog.',
            'recommended_games' => 'Recommended Games',
            'no_recommendations' => 'No recommendations available',
            'all_genres' => 'All Genres',
            'all_platforms' => 'All Platforms',
            'price_from' => 'Price From',
            'price_to' => 'Price To',
            'sort_by' => 'Sort By',
            'default' => 'Default',
            'price_asc' => 'Price: Low to High',
            'price_desc' => 'Price: High to Low',
            'newest' => 'Newest',
            'add_to_wishlist' => 'Add to Wishlist',
            'remove_from_wishlist' => 'Remove from Wishlist',
            'russian' => 'Russian',
            'english' => 'English',
            'light_theme' => 'Light Theme',
            'dark_theme' => 'Dark Theme',
            'order_success' => 'Order #{order_id} successfully placed!',
            'add_review' => 'Add Review',
            'your_review' => 'Your Review',
            'submit_review' => 'Submit Review',
            'report_user' => 'Report User',
            'report_reason' => 'Reason for Report',
            'submit_complaint' => 'Submit Complaint',
            'complaint_submitted' => 'Complaint Submitted',
            'login_to_add_to_cart' => 'Login to add to cart',
            'or' => 'or',
            'new_review' => 'New Review',
            'review_added' => 'Review Successfully Added',
            'cart_updated' => 'Cart Updated',
            'game_added_to_cart' => 'Game "{title}" added to cart',
            'review_complaint' => 'Review Complaint',
            'invalid_rating' => 'Invalid rating',
            'comment_required' => 'Comment required',
            'review_add_error' => 'Error adding review',
            'reason_required' => 'Reason required',
            'complaint_error' => 'Error submitting complaint',
            'invalid_quantity' => 'Invalid quantity',
            'cart_add_error' => 'Error adding to cart',
            'new_game_added' => 'New Game Added',
            'game_added_notification' => 'Game "{title}" added to catalog',
            'order_completed' => 'Order Completed',
            'order_confirmation' => 'Order Confirmation',
            'payment_for_order' => 'Payment for Order',
            'payment_details' => 'Payment Details',
            'payment_method' => 'Payment Method',
            'total_amount' => 'Total Amount',
            'credit_card' => 'Credit Card',
            'paypal' => 'PayPal',
            'qiwi' => 'Qiwi',
            'pay_now' => 'Pay Now',
            'pay_with_paypal' => 'Pay with PayPal',
            'card_number' => 'Card Number',
            'expiry_date' => 'Expiry Date',
            'cvv' => 'CVV',
            'invalid_card_number' => 'Invalid card number',
            'invalid_expiry' => 'Invalid expiry date',
            'invalid_cvv' => 'Invalid CVV',
            'payment_error' => 'Payment error',
            'order_error' => 'Order error',
            'proceed_to_payment' => 'Proceed to Payment',
            'order_summary' => 'Order Summary',
            'pay_order' => 'Pay Order',
            'transaction_id' => 'Transaction ID',
            'delete_notification' => 'Delete Notification',
            'delete_order' => 'Delete Order',
            'cannot_complain_own_review' => 'Cannot complain about your own review',
            'notification_deleted' => 'Notification deleted',
            'order_deleted' => 'Order deleted',
            'delete_notification_confirm' => 'Are you sure you want to delete this notification?',
            'delete_order_confirm' => 'Are you sure you want to delete this order?',
        ]
    ];

    $lang = $_SESSION['preferred_language'] ?? 'ru';
    $translation = $translations[$lang][$key] ?? $key;

    foreach ($replacements as $placeholder => $value) {
        $translation = str_replace("{{$placeholder}}", $value, $translation);
    }

    return $translation;
}


function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function rateLimit() {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'];
    $now = time();
    $window = 60; // 1 minute window
    
    $stmt = $conn->prepare("SELECT request_count, last_request FROM rate_limits WHERE ip_address = ?");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        if ($now - strtotime($result['last_request']) < $window) {
            if ($result['request_count'] > 30) {
                header('HTTP/1.1 429 Too Many Requests');
                die('Too many requests. Please try again later.');
            }
            $new_count = $result['request_count'] + 1;
        } else {
            $new_count = 1;
        }
        
        $stmt = $conn->prepare("UPDATE rate_limits SET request_count = ?, last_request = NOW() WHERE ip_address = ?");
        $stmt->bind_param("is", $new_count, $ip);
    } else {
        $stmt = $conn->prepare("INSERT INTO rate_limits (ip_address, request_count, last_request) VALUES (?, 1, NOW())");
        $stmt->bind_param("s", $ip);
    }
    $stmt->execute();
}

// Функции для работы с уведомлениями
function addNotification($user_id, $title, $message) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $title, $message);
        $stmt->execute();
        
        // Отправка push-уведомления
        sendPushNotification($user_id, $title, $message);
        
        return true;
    } catch (Exception $e) {
        error_log("Error in addNotification: " . $e->getMessage());
        return false;
    }
}

function getUserNotifications($user_id, $limit = null) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
    }
    $stmt = $conn->prepare($sql);
    if ($limit) {
        $stmt->bind_param("ii", $user_id, $limit);
    } else {
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function deleteNotification($notification_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    return $stmt->execute();
}

function markNotificationAsRead($notification_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    return $stmt->execute();
}

// Функции для работы с играми
function getGames($limit = null, $genre = null, $platform = null, $order = null, $price_min = null, $price_max = null) {
    global $conn;
    
    $sql = "SELECT * FROM games WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($genre) {
        $sql .= " AND genre = ?";
        $params[] = $genre;
        $types .= "s";
    }
    
    if ($platform) {
        $sql .= " AND platform = ?";
        $params[] = $platform;
        $types .= "s";
    }
    
    if ($price_min !== null) {
        $sql .= " AND price >= ?";
        $params[] = $price_min;
        $types .= "d";
    }
    
    if ($price_max !== null) {
        $sql .= " AND price <= ?";
        $params[] = $price_max;
        $types .= "d";
    }
    
    if ($order) {
        $sql .= " ORDER BY $order";
    }
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= "i";
    }
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getGameById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function searchGames($query) {
    global $conn;
    $query = "%$query%";
    $stmt = $conn->prepare("SELECT * FROM games WHERE title LIKE ? OR description LIKE ?");
    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Функции для работы с корзиной
function getCartItems($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT ci.*, g.title, g.price, g.image 
                           FROM cart_items ci 
                           JOIN games g ON ci.game_id = g.id 
                           WHERE ci.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function addToCart($user_id, $game_id, $quantity = 1) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        $new_quantity = $existing['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $existing['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart_items (user_id, game_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $game_id, $quantity);
    }
    
    return $stmt->execute();
}

function removeFromCart($user_id, $game_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    return $stmt->execute();
}

function clearCart($user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// Функции для работы с заказами
function createOrder($user_id, $total_amount) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total_amount, status) VALUES (?, NOW(), ?, 'pending')");
    $stmt->bind_param("id", $user_id, $total_amount);
    $stmt->execute();
    return $conn->insert_id;
}

function getOrderItems($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT oi.*, g.title, g.image 
                           FROM order_items oi 
                           JOIN games g ON oi.game_id = g.id 
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserOrders($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT o.*, p.status as payment_status 
                           FROM orders o 
                           LEFT JOIN payments p ON o.id = p.order_id 
                           WHERE o.user_id = ? 
                           ORDER BY o.order_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function deleteOrder($order_id, $user_id) {
    global $conn;
    $conn->begin_transaction();
    try {
        // Удаляем связанные данные
        $stmt = $conn->prepare("DELETE FROM activation_keys WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM payments WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Удаляем сам заказ
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in deleteOrder: " . $e->getMessage());
        return false;
    }
}

// Функции для работы с избранным
function getWishlistItems($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT w.*, g.title, g.price, g.image 
                           FROM wishlist w 
                           JOIN games g ON w.game_id = g.id 
                           WHERE w.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function addToWishlist($user_id, $game_id) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, game_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $game_id);
    return $stmt->execute();
}

function removeFromWishlist($user_id, $game_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    return $stmt->execute();
}

function isGameInWishlist($user_id, $game_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Функции для работы с отзывами
function getGameReviews($game_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT r.*, u.name as user_name 
                           FROM reviews r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.game_id = ? 
                           ORDER BY r.created_at DESC");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function addReview($user_id, $game_id, $rating, $comment) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, game_id, rating, comment, created_at) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $user_id, $game_id, $rating, $comment);
    return $stmt->execute();
}

function addReviewComplaint($user_id, $review_id, $reason) {
    global $conn;
    
    // Проверяем, не является ли пользователь автором отзыва
    $stmt = $conn->prepare("SELECT user_id FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $review = $stmt->get_result()->fetch_assoc();
    
    if ($review && $review['user_id'] == $user_id) {
        return false; // Нельзя жаловаться на свой отзыв
    }
    
    $stmt = $conn->prepare("INSERT INTO review_complaints (review_id, user_id, reason, created_at) 
                           VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $review_id, $user_id, $reason);
    
    if ($stmt->execute()) {
        // Уведомляем пользователя, на чей отзыв подана жалоба
        $stmt = $conn->prepare("SELECT user_id FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        if ($review) {
            addNotification($review['user_id'], t('review_complaint'), t('complaint_submitted') . ": $reason");
        }
        return true;
    }
    return false;
}

// Функции для работы с пользователями
function authenticateUser($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function registerUser($name, $email, $password) {
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    return $stmt->execute();
}

function emailExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function isAdmin($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? (bool)$result['is_admin'] : false;
}

// Функции для работы с ключами активации
function generateActivationKey() {
    $key = strtoupper(bin2hex(random_bytes(16)));
    return [
        'plain' => $key,
        'encrypted' => encryptKey($key)
    ];
}

function getActivationKeys($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT ak.*, g.title 
                           FROM activation_keys ak 
                           JOIN games g ON ak.game_id = g.id 
                           WHERE ak.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $keys = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($keys as &$key) {
        $key['activation_key'] = decryptKey($key['encrypted_key']);
    }
    
    return $keys;
}

// Функция для получения рекомендаций
function getRecommendedGames($user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT DISTINCT g.genre
            FROM wishlist w
            JOIN games g ON w.game_id = g.id
            WHERE w.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $genres = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $genre_list = array_column($genres, 'genre');
        
        if (!empty($genre_list)) {
            $placeholders = implode(',', array_fill(0, count($genre_list), '?'));
            $sql = "
                SELECT g.*
                FROM games g
                WHERE g.genre IN ($placeholders)
                AND g.id NOT IN (
                    SELECT game_id FROM wishlist WHERE user_id = ?
                )
                ORDER BY g.rating DESC
                LIMIT 4
            ";
            $params = array_merge($genre_list, [$user_id]);
            $types = str_repeat('s', count($genre_list)) . 'i';
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $games = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($games)) {
                return $games;
            }
        }
        
        $stmt = $conn->prepare("
            SELECT g.*
            FROM games g
            LEFT JOIN order_items oi ON g.id = oi.game_id
            GROUP BY g.id
            ORDER BY COALESCE(SUM(oi.quantity), 0) DESC, g.rating DESC
            LIMIT 4
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error in getRecommendedGames: " . $e->getMessage());
        return [];
    }
}

// Функция для отправки push-уведомлений
function sendPushNotification($user_id, $title, $body) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT push_token FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && $result['push_token']) {
        // Заглушка для отправки push-уведомлений
        error_log("Push notification sent to user $user_id: $title - $body");
        return true;
    }
    return false;
}
?>