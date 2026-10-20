<?php
require 'config.php';
require 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('transactions.php');
}
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$month = valid_month((string)($_POST['month'] ?? date('Y-m')));
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->execute([$id]);
    set_flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Đã xóa giao dịch.' : 'Không tìm thấy giao dịch cần xóa.');
} else {
    set_flash('error', 'Mã giao dịch không hợp lệ.');
}
redirect('transactions.php?month=' . urlencode($month));
