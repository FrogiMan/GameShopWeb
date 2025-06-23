<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

// Получаем статистику
$stats = [
    'totalGames' => getTotalGames(),
    'totalOrders' => getTotalOrders(),
    'totalUsers' => getTotalUsers(),
    'recentOrders' => getRecentOrders(5),
    'salesData' => getSalesAnalytics(),
    'topGames' => getTopSellingGames(5),
    'revenueByMonth' => getRevenueByMonth()
];

logAdminAction($_SESSION['user_id'], 'accessed_dashboard');

require_once '../includes/header.php';
?>

<section class="admin-dashboard">
    <h2><?= t('admin_panel') ?></h2>
    <p><?= t('welcome') ?>, <?= htmlspecialchars($_SESSION['user_name'] ?? t('admin')) ?>!</p>
    
    <div class="admin-stats-grid">
        <div class="stat-card">
            <h3><?= t('total_games') ?></h3>
            <p><?= $stats['totalGames'] ?></p>
            <a href="games.php" class="btn btn-sm"><?= t('manage') ?></a>
        </div>
        
        <div class="stat-card">
            <h3><?= t('total_orders') ?></h3>
            <p><?= $stats['totalOrders'] ?></p>
            <a href="orders.php" class="btn btn-sm"><?= t('manage') ?></a>
        </div>
        
        <div class="stat-card">
            <h3><?= t('total_users') ?></h3>
            <p><?= $stats['totalUsers'] ?></p>
            <a href="users.php" class="btn btn-sm"><?= t('manage') ?></a>
        </div>
        
        <div class="stat-card">
            <h3><?= t('month_revenue') ?></h3>
            <p><?= number_format(array_sum(array_column($stats['revenueByMonth'], 'revenue')), 2) ?> <?= t('currency') ?></p>
        </div>
    </div>
    
    <div class="charts-container">
        <div class="chart-card">
            <h3><?= t('monthly_revenue') ?></h3>
            <canvas id="revenueChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><?= t('top_games') ?></h3>
            <canvas id="topGamesChart"></canvas>
        </div>
    </div>
    
    <div class="recent-section">
        <div class="recent-orders">
            <h3><?= t('recent_orders') ?></h3>
            <?php if (!empty($stats['recentOrders'])): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th><?= t('order_number') ?></th>
                            <th><?= t('customer') ?></th>
                            <th><?= t('date') ?></th>
                            <th><?= t('amount') ?></th>
                            <th><?= t('status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recentOrders'] as $order): ?>
                            <tr>
                                <td><a href="order_details.php?id=<?= $order['id'] ?>">#<?= $order['id'] ?></a></td>
                                <td><?= htmlspecialchars($order['user_name']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                                <td><?= number_format($order['total_amount'], 2) ?> <?= t('currency') ?></td>
                                <td>
                                    <span class="status-<?= strtolower($order['payment_status'] ?? 'none') ?>">
                                        <?= t($order['payment_status'] ?? 'no_payment') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?= t('no_orders') ?></p>
            <?php endif; ?>
        </div>
        
        <div class="top-games">
            <h3><?= t('top_selling_games') ?></h3>
            <?php if (!empty($stats['topGames'])): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th><?= t('game') ?></th>
                            <th><?= t('sold') ?></th>
                            <th><?= t('revenue') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['topGames'] as $game): ?>
                            <tr>
                                <td><?= htmlspecialchars($game['title']) ?></td>
                                <td><?= $game['total_sold'] ?></td>
                                <td><?= number_format($game['total_revenue'], 2) ?> <?= t('currency') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?= t('no_data') ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Функция для загрузки Chart.js с резервным вариантом
function loadChartJS() {
    return new Promise((resolve, reject) => {
        // Пробуем загрузить с CDN
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = resolve;
        script.onerror = () => {
            // Если CDN не доступен, загружаем локальную версию
            const fallbackScript = document.createElement('script');
            fallbackScript.src = '/assets/js/chart.min.js';
            fallbackScript.onload = resolve;
            fallbackScript.onerror = reject;
            document.head.appendChild(fallbackScript);
        };
        document.head.appendChild(script);
    });
}

// Загружаем библиотеку и затем инициализируем графики
loadChartJS().then(() => {
    // График месячной выручки
    if (document.getElementById('revenueChart')) {
        const revenueData = {
            labels: <?= json_encode(array_map(function($item) { 
                return date('M Y', strtotime($item['month'] . '-01')); 
            }, $stats['revenueByMonth'])) ?>,
            datasets: [{
                label: '<?= t('revenue') ?>',
                data: <?= json_encode(array_column($stats['revenueByMonth'], 'revenue')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        new Chart(document.getElementById('revenueChart').getContext('2d'), {
            type: 'bar',
            data: revenueData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' <?= t('currency') ?>';
                            }
                        }
                    }
                }
            }
        });
    }

    // График топовых игр
    if (document.getElementById('topGamesChart')) {
        const topGamesData = {
            labels: <?= json_encode(array_column($stats['topGames'], 'title')) ?>,
            datasets: [{
                label: '<?= t('sales') ?>',
                data: <?= json_encode(array_column($stats['topGames'], 'total_sold')) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        new Chart(document.getElementById('topGamesChart').getContext('2d'), {
            type: 'bar',
            data: topGamesData,
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}).catch(error => {
    console.error('Не удалось загрузить Chart.js:', error);
    // Можно показать сообщение об ошибке пользователю
    const chartCards = document.querySelectorAll('.chart-card');
    chartCards.forEach(card => {
        card.innerHTML = '<p class="error">Не удалось загрузить графики. Пожалуйста, попробуйте позже.</p>';
    });
});
</script>

<?php
require_once '../includes/footer.php';
?>