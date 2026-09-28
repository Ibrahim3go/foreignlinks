<?php
// Admin shared header — included at top of every admin page
if (!isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/login.php'); exit;
}
$adminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle).' | ' : '' ?>Admin – <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo"
                 onerror="this.style.display='none'" class="sb-logo">
            <div class="sb-text">
                <span class="sb-name">FLC Admin</span>
                <span class="sb-sub"><?= SITE_SESSION ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Main</div>
            <a href="<?= BASE_URL ?>/admin/dashboard.php"
               class="nav-item <?= $adminPage==='dashboard.php'?'active':'' ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/applicants.php"
               class="nav-item <?= $adminPage==='applicants.php'?'active':'' ?>">
                <span class="nav-icon">📋</span> Applications
            </a>
            <a href="<?= BASE_URL ?>/admin/review.php"
               class="nav-item <?= $adminPage==='review.php'?'active':'' ?>">
                <span class="nav-icon">🔍</span> Review &amp; Admit
            </a>
            <a href="<?= BASE_URL ?>/admin/reports.php"
               class="nav-item <?= $adminPage==='reports.php'?'active':'' ?>">
                <span class="nav-icon">📈</span> Reports
            </a>

            <div class="nav-section-label" style="margin-top:12px">Settings</div>
            <a href="<?= BASE_URL ?>/admin/settings.php"
               class="nav-item <?= $adminPage==='settings.php'?'active':'' ?>">
                <span class="nav-icon">⚙️</span> Settings
            </a>
            <a href="<?= BASE_URL ?>/index.php" class="nav-item" target="_blank">
                <span class="nav-icon">🌐</span> Public Portal
            </a>
            <a href="<?= BASE_URL ?>/process/logout.php" class="nav-item danger">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </nav>

        <div class="sidebar-admin-info">
            <div class="sai-avatar"><?= strtoupper(substr($_SESSION['admin_name'],0,2)) ?></div>
            <div class="sai-details">
                <strong><?= e($_SESSION['admin_name']) ?></strong>
                <small><?= e($_SESSION['admin_role']) ?></small>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="admin-main">
        <!-- Top bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('open')" aria-label="Toggle sidebar">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-title"><?= isset($pageTitle) ? e($pageTitle) : 'Admin Panel' ?></div>
            <div class="topbar-right">
                <span class="topbar-session"><?= SITE_SESSION ?></span>
                <a href="<?= BASE_URL ?>/process/logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- Flash -->
        <div style="padding:0 24px"><?= getFlash() ?></div>
