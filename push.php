<?php
require_once 'includes/database.php';
require_once 'includes/functions.php';
session_start();

// VAPID keys
$vapidPublicKey = 'your_vapid_public_key';
$vapidPrivateKey = 'your_vapid_private_key';

function sendPush($subscription, $title, $body) {
    global $vapidPublicKey, $vapidPrivateKey;
    
    $payload = json_encode([
        'title' => $title,
        'body' => $body,
        'icon' => '/assets/images/icon.png'
    ]);
    
    $headers = [
        'TTL' => 2419200,
        'Content-Type' => 'application/json',
        'Authorization' => 'WebPush ' . generateWebPushToken($payload, $subscription)
    ];
    
    $ch = curl_init($subscription['endpoint']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function($key, $value) {
        return "$key: $value";
    }, array_keys($headers), $headers));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
}

function generateWebPushToken($payload, $subscription) {
    global $vapidPrivateKey;
    
    $header = base64url_encode(json_encode([
        'alg' => 'ES256',
        'typ' => 'JWT'
    ]));
    
    $body = base64url_encode(json_encode([
        'aud' => parse_url($subscription['endpoint'], PHP_URL_SCHEME) . '://' . parse_url($subscription['endpoint'], PHP_URL_HOST),
        'exp' => time() + 86400,
        'sub' => 'mailto:admin@gamestore.com'
    ]));
    
    $data = "$header.$body";
    $signature = '';
    openssl_sign($data, $signature, $vapidPrivateKey, OPENSSL_ALGO_SHA256);
    
    return "$data." . base64url_encode($signature);
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    rateLimit();
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'] ?? t('notification');
    $body = $_POST['body'] ?? t('new_notification');
    
    $stmt = $conn->prepare("SELECT push_token FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && $result['push_token']) {
        $subscription = json_decode($result['push_token'], true);
        if (sendPush($subscription, $title, $body)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => t('push_failed')]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => t('no_subscription')]);
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'error' => t('invalid_request')]);
}
?>