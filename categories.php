<?php
require 'config.php';
require 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    if ($action === 'save') {
        $name = trim((string)($_POST['name'] ?? ''));
        $type = (string)($_POST['type'] ?? '');
        $color = (string)($_POST['color'] ?? '#64748b');
        if ($name === '' || mb_strlen($name) > 80 || !in_array($type, ['income', 'expense'], true) || !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            set_flash('error', 'Thông tin danh mục không hợp lệ.');
            redirect('categories.php');
        }
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE categories SET name=?, type=?, color=? WHERE id=?");
                $stmt->execute([$name, $type, $color, $id]);
                set_flash('success', 'Đã cập nhật danh mục.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, type, color) VALUES (?, ?, ?)");
                $stmt->execute([$name, $type, $color]);
                set_flash('success', 'Đã thêm danh mục.');
            }
        } catch (PDOException $e) {
            set_flash('error', 'Tên danh mục này đã tồn tại trong cùng loại.');
        }
        redirect('categories.php');
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            set_flash('error', 'Không thể xóa danh mục đang được sử dụng trong giao dịch. Hãy sửa hoặc xóa các giao dịch liên quan trước.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Đã xóa danh mục.');
        }
        redirect('categories.php');
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$editCategory = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch() ?: null;
}
$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM transactions t WHERE t.category_id = c.id) AS transaction_count FROM categories c ORDER BY c.type, c.name")->fetchAll();
$pageTitle = 'Quản lý danh mục';
require 'header.php';
?>
<section class="category-layout">
    <div class="panel category-form-panel">
        <div class="panel-heading"><div><h2><?= $editCategory ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2><p>Phân loại khoản thu và khoản chi.</p></div></div>
        <form method="post" class="data-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)($editCategory['id'] ?? 0) ?>">
            <div class="field"><label for="name">Tên danh mục <span class="required">*</span></label><input id="name" name="name" maxlength="80" required value="<?= e($editCategory['name'] ?? '') ?>" placeholder="Ví dụ: Tiền ăn"></div>
            <div class="field"><label for="type">Loại danh mục <span class="required">*</span></label><select id="type" name="type" required><option value="expense" <?= ($editCategory['type'] ?? 'expense') === 'expense' ? 'selected' : '' ?>>Chi tiêu</option><option value="income" <?= ($editCategory['type'] ?? '') === 'income' ? 'selected' : '' ?>>Thu nhập</option></select></div>
            <div class="field"><label for="color">Màu đại diện</label><input id="color" name="color" type="color" value="<?= e($editCategory['color'] ?? '#64748b') ?>" class="color-input"></div>
            <div class="form-actions"><button class="button button-primary" type="submit"><?= $editCategory ? 'Lưu thay đổi' : '＋ Thêm danh mục' ?></button><?php if ($editCategory): ?><a class="button button-secondary" href="categories.php">Hủy</a><?php endif; ?></div>
        </form>
    </div>
    <div class="panel category-list-panel">
        <div class="panel-heading"><div><h2>Danh mục hiện có</h2><p><?= count($categories) ?> danh mục</p></div></div>
        <?php if (!$categories): ?><div class="empty-state"><strong>Chưa có danh mục</strong></div><?php else: ?>
        <div class="category-list">
            <?php foreach ($categories as $category): ?>
                <div class="category-row">
                    <span class="category-color" style="background:<?= e($category['color']) ?>"></span>
                    <div class="category-info"><strong><?= e($category['name']) ?></strong><small><?= $category['type'] === 'income' ? 'Thu nhập' : 'Chi tiêu' ?> · <?= (int)$category['transaction_count'] ?> giao dịch</small></div>
                    <a class="icon-button" href="categories.php?edit=<?= (int)$category['id'] ?>" title="Sửa">✎</a>
                    <form method="post" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?');">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                        <button class="icon-button danger-button" type="submit" title="Xóa">×</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require 'footer.php'; ?>
