<?php
require 'config.php';
require 'helpers.php';

$month = valid_month((string)($_GET['month'] ?? date('Y-m')));
$type = (string)($_GET['type'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$keyword = trim((string)($_GET['keyword'] ?? ''));

$sql = "SELECT t.*, c.name AS category_name, c.color AS category_color
        FROM transactions t INNER JOIN categories c ON c.id = t.category_id
        WHERE DATE_FORMAT(t.transaction_date, '%Y-%m') = :month";
$params = ['month' => $month];

if (in_array($type, ['income', 'expense'], true)) {
    $sql .= " AND t.type = :type";
    $params['type'] = $type;
}
if ($categoryId) {
    $sql .= " AND t.category_id = :category_id";
    $params['category_id'] = $categoryId;
}
if ($keyword !== '') {
    $sql .= " AND (t.title LIKE :keyword OR t.note LIKE :keyword)";
    $params['keyword'] = '%' . $keyword . '%';
}
$sql .= " ORDER BY t.transaction_date DESC, t.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$categories = $pdo->query("SELECT id, name, type, color FROM categories ORDER BY type, name")->fetchAll();

$pageTitle = 'Quản lý giao dịch';
require 'header.php';
?>
<section class="panel filter-panel">
    <form method="get" class="filters-form">
        <div class="field"><label for="month">Tháng</label><input id="month" type="month" name="month" value="<?= e($month) ?>"></div>
        <div class="field"><label for="type">Loại giao dịch</label>
            <select id="type" name="type">
                <option value="">Tất cả</option>
                <option value="income" <?= $type === 'income' ? 'selected' : '' ?>>Thu nhập</option>
                <option value="expense" <?= $type === 'expense' ? 'selected' : '' ?>>Chi tiêu</option>
            </select>
        </div>
        <div class="field"><label for="category_id">Danh mục</label>
            <select id="category_id" name="category_id"><option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>" <?= (int)$categoryId === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?> (<?= $category['type'] === 'income' ? 'Thu' : 'Chi' ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field search-field"><label for="keyword">Tìm kiếm</label><input id="keyword" type="search" name="keyword" value="<?= e($keyword) ?>" placeholder="Tên giao dịch hoặc ghi chú"></div>
        <button class="button button-primary filter-submit" type="submit">Lọc</button>
        <a class="button button-secondary" href="transactions.php">Xóa lọc</a>
    </form>
</section>

<section class="panel transactions-panel">
    <div class="panel-heading">
        <div><h2>Danh sách giao dịch</h2><p>Tìm thấy <?= count($transactions) ?> giao dịch trong tháng <?= date('m/Y', strtotime($month . '-01')) ?></p></div>
        <a class="button button-primary" href="transaction_form.php">＋ Thêm mới</a>
    </div>
    <?php if (!$transactions): ?>
        <div class="empty-state"><div class="empty-symbol">⌕</div><strong>Không tìm thấy giao dịch</strong><p>Thử đổi bộ lọc hoặc thêm giao dịch mới.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Giao dịch</th><th>Danh mục</th><th>Ngày</th><th>Loại</th><th class="text-right">Số tiền</th><th class="text-right">Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><div class="transaction-title"><?= e($transaction['title']) ?></div><?php if ($transaction['note']): ?><div class="transaction-note"><?= e($transaction['note']) ?></div><?php endif; ?></td>
                        <td><span class="category-pill"><span class="legend-dot" style="background:<?= e($transaction['category_color']) ?>"></span><?= e($transaction['category_name']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                        <td><span class="type-pill <?= $transaction['type'] === 'income' ? 'type-income' : 'type-expense' ?>"><?= $transaction['type'] === 'income' ? 'Thu nhập' : 'Chi tiêu' ?></span></td>
                        <td class="text-right amount <?= $transaction['type'] === 'income' ? 'income-text' : 'expense-text' ?>"><?= $transaction['type'] === 'income' ? '+' : '−' ?><?= money($transaction['amount']) ?></td>
                        <td><div class="action-buttons">
                            <a class="icon-button" title="Sửa" href="transaction_form.php?id=<?= (int)$transaction['id'] ?>">✎</a>
                            <form method="post" action="transaction_delete.php" onsubmit="return confirm('Bạn có chắc muốn xóa giao dịch này?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$transaction['id'] ?>">
                                <input type="hidden" name="month" value="<?= e($month) ?>">
                                <button class="icon-button danger-button" title="Xóa" type="submit">×</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require 'footer.php'; ?>
