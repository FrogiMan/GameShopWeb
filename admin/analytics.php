<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: /login.php");
    exit;
}

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$analytics = getSalesAnalytics($start_date, $end_date);

logAdminAction($_SESSION['user_id'], 'accessed_analytics', "Date range: $start_date to $end_date");

require_once '../includes/header.php';
?>

<section class="admin-analytics">
    <h2><?= t('sales_analytics') ?></h2>
    
    <div class="filters">
        <form method="get">
            <div class="filter-group">
                <label for="start_date"><?= t('start_date') ?>:</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>">
            </div>
            <div class="filter-group">
                <label for="end_date"><?= t('end_date') ?>:</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>">
            </div>
            <button type="submit" class="btn"><?= t('apply_filters') ?></button>
            <a href="analytics.php" class="btn btn-outline"><?= t('reset') ?></a>
        </form>
    </div>
    
    <?php if (!empty($analytics)): ?>
        <div class="charts">
            <div class="chart-container">
                <h3><?= t('sales_by_game') ?></h3>
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-container">
                <h3><?= t('revenue_by_game') ?></h3>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        
        <div class="analytics-table">
            <h3><?= t('detailed_report') ?></h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= t('game') ?></th>
                        <th><?= t('total_sold') ?></th>
                        <th><?= t('total_revenue') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td><?= $item['total_sold'] ?></td>
                            <td><?= number_format($item['total_revenue'], 2) ?> <?= t('currency') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p><?= t('no_sales_data') ?></p>
    <?php endif; ?>
</section>

<style>
.filters { margin-bottom: 20px; }
.filter-group { display: inline-block; margin-right: 10px; }
.charts { display: flex; flex-wrap: wrap; gap: 20px; }
.chart-container { flex: 1; min-width: 300px; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 10px; border: 1px solid #ddd; }
.error { color: red; margin-bottom: 10px; }
</style>

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
    const salesData = <?= json_encode($analytics) ?>;
    
    // График продаж по играм
    if (document.getElementById('salesChart')) {
        new Chart(document.getElementById('salesChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: salesData.map(item => item.title),
                datasets: [{
                    label: '<?= t('total_sold') ?>',
                    data: salesData.map(item => item.total_sold),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // График выручки по играм
    if (document.getElementById('revenueChart')) {
        new Chart(document.getElementById('revenueChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: salesData.map(item => item.title),
                datasets: [{
                    label: '<?= t('total_revenue') ?>',
                    data: salesData.map(item => item.total_revenue),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    }
}).catch(error => {
    console.error('Не удалось загрузить Chart.js:', error);
    const chartContainers = document.querySelectorAll('.chart-container');
    chartContainers.forEach(container => {
        container.innerHTML = '<p class="error">Не удалось загрузить графики. Пожалуйста, попробуйте позже.</p>';
    });
});
</script>

<?php
require_once '../includes/footer.php';
?>