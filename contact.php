<?php
// contact.php
require_once 'includes/header.php';
require_once 'includes/functions.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Все поля обязательны для заполнения.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Пожалуйста, введите корректный email.";
    } else {
        $success = true;
    }
}
?>

<section class="contact">
    <h2>Свяжитесь с нами</h2>
    
    <?php if ($success): ?>
        <div class="success">
            Спасибо за ваше сообщение! Мы свяжемся с вами в ближайшее время.
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="contact-container">
            <div class="contact-form">
                <h3>Форма обратной связи</h3>
                <form method="post">
                    <div class="form-group">
                        <label for="name">Ваше имя:</label>
                        <input type="text" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Ваш email:</label>
                        <input type="email" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Ваше сообщение:</label>
                        <textarea id="message" name="message" rows="5" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                    </div>
                    <button type="submit" class="btn">Отправить сообщение</button>
                </form>
            </div>
            
            <div class="contact-info">
                <h3>Контактная информация</h3>
                <div class="info-item">
                    <h4>Адрес</h4>
                    <p>г. Москва, ул. Покровка, д. 42, офис 15</p>
                </div>
                <div class="info-item">
                    <h4>Телефон</h4>
                    <p>+7 (495) 123-45-67</p>
                </div>
                <div class="info-item">
                    <h4>Email</h4>
                    <p>info@gamestore.com</p>
                </div>
                <div class="info-item">
                    <h4>Часы работы</h4>
                    <p>Пн-Пт: 9:00 - 18:00</p>
                    <p>Сб-Вс: 10:00 - 16:00</p>
                </div>
                
                <h3>Мы в социальных сетях</h3>
                <div class="social-links">
                    <a href="#" class="social-link">VK</a>
                    <a href="#" class="social-link">Telegram</a>
                    <a href="#" class="social-link">YouTube</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php
require_once 'includes/footer.php';
?>