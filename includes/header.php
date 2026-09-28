<?php
// ============================================================
//  Foreign Links Campus – Shared Header
//  /includes/header.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Determine active page for nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn  = isApplicantLoggedIn();

// Unread notification count
$notifCount = 0;
if ($isLoggedIn) {
    require_once dirname(__DIR__) . '/includes/db.php';
    $notifCount = countUnreadNotifications($pdo, $_SESSION['applicant_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= SITE_NAME ?> – Online Admission Portal. Apply for NCE, ND/HND, Nursing, Degree and Foreign University programmes.">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= SITE_NAME ?> Admission Portal</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon.png" type="image/png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body class="<?= isset($bodyClass) ? e($bodyClass) : '' ?>">

<!-- ── Top Bar ──────────────────────────────────────────── -->
<div class="topbar">
    <div class="container topbar-inner">
        <span><i class="icon-phone"></i> <?= SITE_PHONE ?></span>
        <span><i class="icon-email"></i> <?= SITE_EMAIL ?></span>
        <span><i class="icon-map"></i> Moro, Ife-North, Osun State</span>
        <span class="topbar-session">
            <?= ADMISSION_OPEN
                ? '<span class="badge-open">Admission Open – ' . SITE_SESSION . '</span>'
                : '<span class="badge-closed">Admission Closed</span>' ?>
        </span>
    </div>
</div>

<!-- ── Main Navigation ──────────────────────────────────── -->
<nav class="navbar" id="mainNav">
    <div class="container navbar-inner">

        <!-- Logo -->
        <a href="<?= BASE_URL ?>/index.php" class="navbar-brand">
            <img src="<?= BASE_URL ?>/assets/images/logo.png"
                 alt="<?= SITE_NAME ?> Logo" class="navbar-logo"
                 onerror="this.style.display='none'">
            <div class="brand-text">
                <span class="brand-name"><?= SITE_NAME ?></span>
                <span class="brand-sub">Moro, Ife-North · Osun State</span>
            </div>
        </a>

        <!-- Mobile Toggle -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <!-- Nav Links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= BASE_URL ?>/index.php"
                   class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a></li>

            <li class="has-dropdown">
                <a href="#">Programmes <span class="caret">▾</span></a>
                <ul class="dropdown">
                    <li><a href="<?= BASE_URL ?>/index.php?college=COE">College of Education (NCE)</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=CHT">College of Health Technology</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=SON">School of Nursing</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=ADP">Affiliate Degree Programmes</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?college=FUL">Foreign University Linkage</a></li>
                </ul>
            </li>

            <li><a href="<?= BASE_URL ?>/apply.php"
                   class="<?= $currentPage === 'apply.php' ? 'active' : '' ?>">Apply Now</a></li>

            <li><a href="<?= BASE_URL ?>/status.php"
                   class="<?= $currentPage === 'status.php' ? 'active' : '' ?>">Check Status</a></li>

            <li><a href="<?= BASE_URL ?>/index.php#contact">Contact</a></li>
        </ul>

        <!-- Auth Buttons -->
        <div class="nav-auth">
            <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline btn-sm">
                    My Dashboard
                    <?php if ($notifCount > 0): ?>
                        <span class="notif-badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/process/logout.php" class="btn btn-ghost btn-sm">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="<?= BASE_URL ?>/apply.php" class="btn btn-primary btn-sm">Apply Now</a>
            <?php endif; ?>
        </div>

    </div>
</nav>
<!-- End Navbar -->

<!-- Flash Message -->
<div class="flash-container">
    <?= getFlash() ?>
</div>
