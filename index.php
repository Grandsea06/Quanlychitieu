<?php
require 'config.php';
require 'helpers.php';

$month = valid_month((string)($_GET['month'] ?? date('Y-m')));
$startDate = $month . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense
    FROM transactions
    WHERE transaction_date BETWEEN :start_date AND :end_date
");
$stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
$totals = $stmt->fetch();
$totalIncome = (float)$totals['total_income'];
$totalExpense = (float)$totals['total_expense'];
$balance = $totalIncome - $totalExpense;

$stmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name, c.color AS category_color
    FROM transactions t
    INNER JOIN categories c ON c.id = t.category_id
    WHERE t.transaction_date BETWEEN :start_date AND :end_date
    ORDER BY t.transaction_date DESC, t.id DESC
    LIMIT 7
");
$stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
$recentTransactions = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT c.name, c.color, SUM(t.amount) AS total
    FROM transactions t
    INNER JOIN categories c ON c.id = t.category_id
    WHERE t.type = 'expense'
      AND t.transaction_date BETWEEN :start_date AND :end_date
    GROUP BY c.id, c.name, c.color
    ORDER BY total DESC
");
$stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
$categoryStats = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT DAY(transaction_date) AS day_number,
           SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
           SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
    FROM transactions
    WHERE transaction_date BETWEEN :start_date AND :end_date
    GROUP BY DAY(transaction_date)
    ORDER BY day_number
");
$stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
$dailyStats = $stmt->fetchAll();

$pageTitle = 'Tổng quan';
require 'header.php';
?>
<section class="filter-bar">
    <form method="get" class="month-filter">
        <label for="month">Tháng thống kê</label>
        <input id="month" type="month" name="month" value="<?= e($month) ?>">
        <button class="button button-secondary" type="submit">Xem thống kê</button>
    </form>
    <span class="muted">Dữ liệu từ <?= date('d/m/Y', strtotime($startDate)) ?> đến <?= date('d/m/Y', strtotime($endDate)) ?></span>
</section>

<section class="summary-grid">
    <article class="summary-card balance-card">
        <div class="summary-top"><span>Số dư trong tháng</span><span class="summary-icon">₫</span></div>
        <div class="summary-value"><?= money($balance) ?></div>
        <div class="summary-caption">Tổng thu trừ tổng chi</div>
    </article>
    <article class="summary-card">
        <div class="summary-top"><span>Tổng thu</span><span class="summary-icon income-icon">↙</span></div>
        <div class="summary-value income-text"><?= money($totalIncome) ?></div>
        <div class="summary-caption">Các khoản tiền nhận được</div>
    </article>
    <article class="summary-card">
        <div class="summary-top"><span>Tổng chi</span><span class="summary-icon expense-icon">↗</span></div>
        <div class="summary-value expense-text"><?= money($totalExpense) ?></div>
        <div class="summary-caption">Các khoản tiền đã chi</div>
    </article>
</section>

<section class="charts-grid">
    <article class="panel chart-panel">
        <div class="panel-heading">
            <div><h2>Thu và chi theo ngày</h2><p>Diễn biến giao dịch trong tháng</p></div>
        </div>
        <?php if (!$dailyStats): ?>
            <div class="empty-chart">Chưa có dữ liệu trong tháng này. Hãy thêm giao dịch để xem biểu đồ.</div>
        <?php else: ?>
            <div class="chart-wrap"><canvas id="dailyChart"></canvas></div>
        <?php endif; ?>
    </article>
    <article class="panel chart-panel">
        <div class="panel-heading">
            <div><h2>Chi tiêu theo danh mục</h2><p>Các nhóm chi tiêu trong tháng</p></div>
        </div>
        <?php if (!$categoryStats): ?>
            <div class="empty-chart">Chưa có khoản chi nào trong tháng này.</div>
        <?php else: ?>
            <div class="doughnut-wrap"><canvas id="categoryChart"></canvas></div>
            <div class="legend-list">
                <?php foreach ($categoryStats as $stat): ?>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:<?= e($stat['color']) ?>"></span>
                        <span><?= e($stat['name']) ?></span>
                        <strong><?= money($stat['total']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<section class="panel transactions-panel">
    <div class="panel-heading">
        <div><h2>Giao dịch gần đây</h2><p>7 giao dịch mới nhất của tháng đã chọn</p></div>
        <a class="text-link" href="transactions.php?month=<?= e($month) ?>">Xem tất cả →</a>
    </div>
    <?php if (!$recentTransactions): ?>
        <div class="empty-state">
            <div class="empty-symbol">↔</div>
            <strong>Chưa có giao dịch nào</strong>
            <p>Thêm khoản thu hoặc chi đầu tiên để bắt đầu theo dõi tài chính.</p>
            <a class="button button-primary" href="transaction_form.php">＋ Thêm giao dịch</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Giao dịch</th><th>Danh mục</th><th>Ngày</th><th>Loại</th><th class="text-right">Số tiền</th></tr></thead>
                <tbody>
                <?php foreach ($recentTransactions as $transaction): ?>
                    <tr>
                        <td><div class="transaction-title"><?= e($transaction['title']) ?></div>
                            <?php if ($transaction['note']): ?><div class="transaction-note"><?= e($transaction['note']) ?></div><?php endif; ?></td>
                        <td><span class="category-pill"><span class="legend-dot" style="background:<?= e($transaction['category_color']) ?>"></span><?= e($transaction['category_name']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                        <td><span class="type-pill <?= $transaction['type'] === 'income' ? 'type-income' : 'type-expense' ?>"><?= $transaction['type'] === 'income' ? 'Thu nhập' : 'Chi tiêu' ?></span></td>
                        <td class="text-right amount <?= $transaction['type'] === 'income' ? 'income-text' : 'expense-text' ?>"><?= $transaction['type'] === 'income' ? '+' : '−' ?><?= money($transaction['amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($dailyStats): ?>
<script>
const dailyRows = <?= json_encode($dailyStats, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const daysInMonth = <?= (int)date('t', strtotime($startDate)) ?>;
const dayMap = {};
dailyRows.forEach(row => dayMap[Number(row.day_number)] = row);
const labels = Array.from({length: daysInMonth}, (_, i) => String(i + 1));
const incomeData = labels.map((_, i) => dayMap[i + 1] ? Number(dayMap[i + 1].income) : 0);
const expenseData = labels.map((_, i) => dayMap[i + 1] ? Number(dayMap[i + 1].expense) : 0);
const dailyCanvas = document.getElementById('dailyChart');
if (dailyCanvas) new Chart(dailyCanvas, {
    type: 'line',
    data: { labels, datasets: [
        { label: 'Thu', data: incomeData, borderColor: '#16a36a', backgroundColor: 'rgba(22,163,106,.10)', fill: true, tension: .3, pointRadius: 2 },
        { label: 'Chi', data: expenseData, borderColor: '#e45d63', backgroundColor: 'rgba(228,93,99,.08)', fill: true, tension: .3, pointRadius: 2 }
    ]},
    options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' },
        plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.parsed.y) + ' ₫' } } },
        scales: { y: { beginAtZero: true, ticks: { callback: value => new Intl.NumberFormat('vi-VN', {notation:'compact'}).format(value) } }, x: { grid: { display: false } } }
    }
});
</script>
<?php endif; ?>
<?php if ($categoryStats): ?>
<script>
const categoryRows = <?= json_encode($categoryStats, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const categoryCanvas = document.getElementById('categoryChart');
if (categoryCanvas) new Chart(categoryCanvas, {
    type: 'doughnut',
    data: { labels: categoryRows.map(row => row.name), datasets: [{
        data: categoryRows.map(row => Number(row.total)),
        backgroundColor: categoryRows.map(row => row.color),
        borderWidth: 0,
        hoverOffset: 5
    }]},
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.parsed) + ' ₫' } } }
    }
});
</script>
<?php endif; ?>
<?php require 'footer.php'; ?>
