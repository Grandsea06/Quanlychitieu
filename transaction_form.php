<?php
require 'config.php';
require 'helpers.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$transaction = [
    'title' => '', 'type' => 'expense', 'category_id' => '',
    'amount' => '', 'transaction_date' => date('Y-m-d'), 'note' => ''
];
$isEdit = false;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        set_flash('error', 'Không tìm thấy giao dịch cần sửa.');
        redirect('transactions.php');
    }
    $transaction = $existing;
    $isEdit = true;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $transaction['title'] = trim((string)($_POST['title'] ?? ''));
    $transaction['type'] = (string)($_POST['type'] ?? 'expense');
    $transaction['category_id'] = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: '';
    $transaction['amount'] = trim((string)($_POST['amount'] ?? ''));
    $transaction['transaction_date'] = (string)($_POST['transaction_date'] ?? '');
    $transaction['note'] = trim((string)($_POST['note'] ?? ''));

    if ($transaction['title'] === '' || mb_strlen($transaction['title']) > 150) {
        $errors[] = 'Tên giao dịch không được để trống và tối đa 150 ký tự.';
    }
    if (!in_array($transaction['type'], ['income', 'expense'], true)) {
        $errors[] = 'Loại giao dịch không hợp lệ.';
    }
    if (!is_numeric($transaction['amount']) || (float)$transaction['amount'] <= 0 || (float)$transaction['amount'] > 999999999999) {
        $errors[] = 'Số tiền phải lớn hơn 0 và nằm trong giới hạn cho phép.';
    }
    $date = DateTime::createFromFormat('Y-m-d', $transaction['transaction_date']);
    if (!$date || $date->format('Y-m-d') !== $transaction['transaction_date']) {
        $errors[] = 'Ngày giao dịch không hợp lệ.';
    }

    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND type = ?");
    $stmt->execute([(int)$transaction['category_id'], $transaction['type']]);
    if (!$stmt->fetch()) {
        $errors[] = 'Hãy chọn danh mục đúng với loại giao dịch.';
    }

    if (!$errors) {
        $data = [
            'title' => $transaction['title'],
            'type' => $transaction['type'],
            'category_id' => (int)$transaction['category_id'],
            'amount' => (float)$transaction['amount'],
            'transaction_date' => $transaction['transaction_date'],
            'note' => $transaction['note'] !== '' ? $transaction['note'] : null,
        ];
        if ($isEdit) {
            $data['id'] = $id;
            $stmt = $pdo->prepare("UPDATE transactions SET title=:title, type=:type, category_id=:category_id, amount=:amount, transaction_date=:transaction_date, note=:note WHERE id=:id");
        } else {
            $stmt = $pdo->prepare("INSERT INTO transactions (title, type, category_id, amount, transaction_date, note) VALUES (:title, :type, :category_id, :amount, :transaction_date, :note)");
        }
        $stmt->execute($data);
        set_flash('success', $isEdit ? 'Đã cập nhật giao dịch.' : 'Đã thêm giao dịch mới.');
        redirect('transactions.php?month=' . substr($transaction['transaction_date'], 0, 7));
    }
}

$categories = $pdo->query("SELECT id, name, type FROM categories ORDER BY name")->fetchAll();
$pageTitle = $isEdit ? 'Sửa giao dịch' : 'Thêm giao dịch';
require 'header.php';
?>
<section class="form-layout">
    <div class="panel form-panel">
        <div class="panel-heading"><div><h2>Thông tin giao dịch</h2><p>Nhập thông tin khoản thu hoặc chi của bạn.</p></div></div>
        <?php if ($errors): ?><div class="alert alert-error"><strong>Vui lòng kiểm tra:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" class="data-form" id="transactionForm">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="field"><label for="title">Tên giao dịch <span class="required">*</span></label><input id="title" name="title" maxlength="150" required value="<?= e($transaction['title']) ?>" placeholder="Ví dụ: Ăn trưa, lương tháng 10"></div>
            <div class="field"><label>Loại giao dịch <span class="required">*</span></label>
                <div class="type-choice">
                    <label class="choice-option"><input type="radio" name="type" value="expense" <?= $transaction['type'] === 'expense' ? 'checked' : '' ?>><span>↗ Chi tiêu</span></label>
                    <label class="choice-option"><input type="radio" name="type" value="income" <?= $transaction['type'] === 'income' ? 'checked' : '' ?>><span>↙ Thu nhập</span></label>
                </div>
            </div>
            <div class="field"><label for="category_id">Danh mục <span class="required">*</span></label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>" data-type="<?= e($category['type']) ?>" <?= (string)$transaction['category_id'] === (string)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?> (<?= $category['type'] === 'income' ? 'Thu' : 'Chi' ?>)</option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$categories): ?><small class="field-help">Bạn cần tạo danh mục trước khi thêm giao dịch.</small><?php endif; ?>
            </div>
            <div class="field"><label for="amount">Số tiền (VNĐ) <span class="required">*</span></label><input id="amount" name="amount" type="number" min="1" step="1" required value="<?= e($transaction['amount']) ?>" placeholder="Ví dụ: 50000"></div>
            <div class="field"><label for="transaction_date">Ngày giao dịch <span class="required">*</span></label><input id="transaction_date" name="transaction_date" type="date" required value="<?= e($transaction['transaction_date']) ?>"></div>
            <div class="field"><label for="note">Ghi chú</label><textarea id="note" name="note" rows="3" placeholder="Thông tin thêm (không bắt buộc)"><?= e($transaction['note']) ?></textarea></div>
            <div class="form-actions"><button class="button button-primary" type="submit"><?= $isEdit ? 'Lưu thay đổi' : '＋ Lưu giao dịch' ?></button><a class="button button-secondary" href="transactions.php">Hủy</a></div>
        </form>
    </div>
    <aside class="panel tip-panel"><div class="tip-icon">i</div><h3>Mẹo nhỏ</h3><p>Ghi tên giao dịch ngắn gọn để dễ tìm kiếm sau này.</p><p>Hãy chọn đúng loại thu hoặc chi để thống kê được chính xác.</p><p>Bạn có thể bổ sung danh mục mới trong mục <strong>Danh mục</strong>.</p></aside>
</section>
<script>
const typeRadios = document.querySelectorAll('input[name="type"]');
const categorySelect = document.getElementById('category_id');
function filterCategories() {
    const selectedType = document.querySelector('input[name="type"]:checked').value;
    let selectedStillValid = false;
    Array.from(categorySelect.options).forEach(option => {
        if (!option.value) { option.hidden = false; return; }
        option.hidden = option.dataset.type !== selectedType;
        if (option.selected && !option.hidden) selectedStillValid = true;
    });
    if (!selectedStillValid) categorySelect.value = '';
}
typeRadios.forEach(radio => radio.addEventListener('change', filterCategories));
filterCategories();
</script>
<?php require 'footer.php'; ?>
