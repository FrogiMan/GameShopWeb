<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

$game = [
    'id' => 0,
    'title' => '',
    'genre' => '',
    'platform' => '',
    'price' => '',
    'description' => '',
    'release_date' => date('Y-m-d'),
    'rating' => 0,
    'image' => ''
];

$isEdit = false;
$error = '';
$success = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $game_id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $game = $stmt->get_result()->fetch_assoc();
        if ($game) {
            $isEdit = true;
        } else {
            $error = 'Игра не найдена';
        }
    } catch (Exception $e) {
        $error = 'Ошибка загрузки данных игры';
        error_log($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    rateLimit();
    
    try {
        $game['title'] = filter_var(trim($_POST['title']), FILTER_SANITIZE_STRING);
        $game['genre'] = filter_var(trim($_POST['genre']), FILTER_SANITIZE_STRING);
        $game['platform'] = filter_var(trim($_POST['platform']), FILTER_SANITIZE_STRING);
        $game['price'] = (float)$_POST['price'];
        $game['description'] = filter_var(trim($_POST['description']), FILTER_SANITIZE_STRING);
        $game['release_date'] = $_POST['release_date'];
        $game['rating'] = max(0, min(10, (int)$_POST['rating']));
        
        // Validate inputs
        if (empty($game['title']) || empty($game['genre']) || empty($game['platform']) || empty($game['description'])) {
            throw new Exception('Все поля обязательны для заполнения');
        }
        
        if ($game['price'] < 0) {
            throw new Exception('Цена не может быть отрицательной');
        }
        
        // Validate release date
        if (!DateTime::createFromFormat('Y-m-d', $game['release_date'])) {
            throw new Exception('Неверный формат даты');
        }
        
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../assets/images/games/';
            if (!is_writable($uploadDir)) {
                throw new Exception('Директория загрузки недоступна для записи');
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Недопустимый формат файла');
            }
            
            if ($_FILES['image']['size'] > 5000000) {
                throw new Exception('Файл слишком большой');
            }
            
            // Validate image content
            if (!getimagesize($_FILES['image']['tmp_name'])) {
                throw new Exception('Загруженный файл не является изображением');
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $game['image'] = $fileName;
                chmod($targetPath, 0644); // Set proper permissions
            } else {
                throw new Exception('Ошибка загрузки изображения');
            }
        } elseif ($isEdit && !empty($game['image'])) {
            // Preserve existing image if no new image is uploaded
            $game['image'] = $game['image'];
        } else {
            throw new Exception('Изображение обязательно для новой игры');
        }
        
        $conn->begin_transaction();
        
        if ($isEdit) {
            $stmt = $conn->prepare("UPDATE games SET title = ?, genre = ?, platform = ?, price = ?, 
                                   description = ?, release_date = ?, rating = ?, image = ?
                                   WHERE id = ?");
            $stmt->bind_param("sssdsdssi", $game['title'], $game['genre'], $game['platform'], 
                            $game['price'], $game['description'], $game['release_date'], 
                            $game['rating'], $game['image'], $game['id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO games (title, genre, platform, price, description, 
                                   release_date, rating, image)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdsdss", $game['title'], $game['genre'], $game['platform'], 
                            $game['price'], $game['description'], $game['release_date'], 
                            $game['rating'], $game['image']);
        }
        
        if ($stmt->execute()) {
            $conn->commit();
            $success = $isEdit ? 'Игра успешно обновлена' : 'Игра успешно добавлена';
            logAdminAction($_SESSION['user_id'], $isEdit ? 'edit_game' : 'add_game', "Game ID: {$game['id']}, Title: {$game['title']}");
            header("Location: games.php");
            exit;
        } else {
            throw new Exception('Ошибка сохранения игры');
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
        error_log($e->getMessage());
    }
}

require_once '../includes/header.php';
?>

<section class="game-edit">
    <h2><?= $isEdit ? 'Редактирование игры' : 'Добавление новой игры' ?></h2>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <div class="form-group">
            <label for="title">Название:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($game['title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="genre">Жанр:</label>
            <input type="text" id="genre" name="genre" value="<?= htmlspecialchars($game['genre']) ?>" required>
        </div>
        <div class="form-group">
            <label for="platform">Платформа:</label>
            <select id="platform" name="platform" required>
                <option value="PC" <?= $game['platform'] == 'PC' ? 'selected' : '' ?>>PC</option>
                <option value="PlayStation" <?= $game['platform'] == 'PlayStation' ? 'selected' : '' ?>>PlayStation</option>
                <option value="Xbox" <?= $game['platform'] == 'Xbox' ? 'selected' : '' ?>>Xbox</option>
                <option value="Nintendo" <?= $game['platform'] == 'Nintendo' ? 'selected' : '' ?>>Nintendo</option>
                <option value="Mobile" <?= $game['platform'] == 'Mobile' ? 'selected' : '' ?>>Mobile</option>
            </select>
        </div>
        <div class="form-group">
            <label for="price">Цена (руб.):</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= $game['price'] ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Описание:</label>
            <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($game['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="release_date">Дата выхода:</label>
            <input type="date" id="release_date" name="release_date" value="<?= $game['release_date'] ?>" required>
        </div>
        <div class="form-group">
            <label for="rating">Рейтинг (0-10):</label>
            <input type="number" id="rating" name="rating" min="0" max="10" value="<?= $game['rating'] ?>" required>
        </div>
        <div class="form-group">
            <label for="image">Изображение:</label>
            <input type="file" id="image" name="image" <?= !$isEdit ? 'required' : '' ?> accept="image/jpeg,image/png,image/gif">
            <?php if ($isEdit && $game['image']): ?>
                <p>Текущее изображение:</p>
                <img src="../assets/images/games/<?= htmlspecialchars($game['image']) ?>" width="150" alt="Current image">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn">Сохранить</button>
        <a href="games.php" class="btn btn-outline">Отмена</a>
    </form>
</section>

<style>
.error { color: red; padding: 10px; background: #ffe6e6; border-radius: 4px; margin-bottom: 15px; }
.success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; margin-bottom: 15px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; }
.form-group input, .form-group select, .form-group textarea { 
    width: 100%; 
    padding: 8px; 
    border: 1px solid #ddd; 
    border-radius: 4px; 
}
</style>

<?php
require_once '../includes/footer.php';
?>