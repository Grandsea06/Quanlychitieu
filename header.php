<?php
$pageTitle = $pageTitle ?? 'Quản lý chi tiêu';
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Sổ Chi Tiêu</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="index.php">
            <span class="brand-icon">₫</span>
            <span><strong>Sổ Chi Tiêu</strong><small>Quản lý tài chính cá nhân</small></span>
        </a>
        <nav class="nav-menu">
            <a class="<?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php"><span>▦</span> Tổng quan</a>
            <a class="<?= in_array($currentPage, ['transactions.php', 'transaction_form.php'], true) ? 'active' : '' ?>" href="transactions.php"><span>⇄</span> Giao dịch</a>
            <a class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>" href="categories.php"><span>▤</span> Danh mục</a>
        </nav>
        <div class="sidebar-note">
            <div class="note-icon">✓</div>
            <strong>Chi tiêu có kế hoạch</strong>
            <p>Theo dõi từng khoản nhỏ để hiểu rõ tình hình tài chính của bạn.</p>
        </div>
        <div class="sidebar-bottom">Phiên bản bài tập lớn PHP &amp; MySQL</div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <div class="eyebrow">TÀI CHÍNH CÁ NHÂN</div>
                <h1><?= e($pageTitle) ?></h1>
            </div>
            <a class="button button-primary" href="transaction_form.php"><span>＋</span> Thêm giao dịch</a>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
