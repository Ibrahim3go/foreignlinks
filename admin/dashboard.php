<?php
// ============================================================
//  Foreign Links Campus – Admin Dashboard
//  /admin/dashboard.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

// ── Stats ────────────────────────────────────────────────────
$stats = [];

// Total applications
$stats['total'] = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// By status
foreach (['Pending','Under Review','Shortlisted','Admitted','Rejected'] as $s) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE status=?");
    $st->execute([$s]);
    $stats[strtolower(str_replace(' ','_',$s))] = $st->fetchColumn();
}

// Total applicants
$stats['applicants'] = $pdo->query("SELECT COUNT(*) FROM applicants")->fetchColumn();

// Payments pending verification
$stats['payments_pending'] = $pdo->query(
    "SELECT COUNT(*) FROM payments WHERE status='Pending'")->fetchColumn();

// By college
$byCollege = $pdo->query("
    SELECT c.code, c.name, COUNT(a.id) as total,
           SUM(a.status='Admitted') as admitted,
           SUM(a.status='Pending')  as pending
    FROM   colleges c
    LEFT JOIN applications a ON c.id = a.college_id
    GROUP  BY c.id ORDER BY c.id
")->fetchAll();

// Recent applications (last 10)
$recent = $pdo->query("
    SELECT a.id, a.status, a.created_at, a.submitted_at,
           ap.ref_number, ap.surname, ap.firstname,
           p.name AS programme_name, c.code AS college_code
    FROM   applications a
    JOIN   applicants ap  ON a.applicant_id  = ap.id
    JOIN   programmes p   ON a.programme_id  = p.id
    JOIN   colleges c     ON a.college_id    = c.id
    ORDER  BY a.created_at DESC LIMIT 10
")->fetchAll();

// Recent payments pending
$pendingPay = $pdo->query("
    SELECT py.*, ap.surname, ap.firstname, ap.ref_number
    FROM   payments py
    JOIN   applicants ap ON py.applicant_id = ap.id
    WHERE  py.status = 'Pending'
    ORDER  BY py.created_at DESC LIMIT 8
")->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once dirname(__DIR__) . '/admin/partials/admin-header.php';
?>

<div class="admin-content">

    <!-- Page title -->
    <div class="admin-page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, <strong><?= e($_SESSION['admin_name']) ?></strong> &mdash; <?= date('l, d F Y') ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?= BASE_URL ?>/admin/applicants.php" class="btn btn-primary btn-sm">
                View All Applications
            </a>
        </div>
    </div>

    <?= getFlash() ?>

    <!-- Stat Cards -->
    <div class="stat-cards">
        <div class="stat-card blue">
            <div class="sc-icon">📋</div>
            <div class="sc-body">
                <div class="sc-number"><?= number_format($stats['total']) ?></div>
                <div class="sc-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="sc-icon">⏳</div>
            <div class="sc-body">
                <div class="sc-number"><?= number_format($stats['pending']) ?></div>
                <div class="sc-label">Pending Review</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="sc-icon">🎓</div>
            <div class="sc-body">
                <div class="sc-number"><?= number_format($stats['admitted']) ?></div>
                <div class="sc-label">Admitted</div>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="sc-icon">👥</div>
            <div class="sc-body">
                <div class="sc-number"><?= number_format($stats['applicants']) ?></div>
                <div class="sc-label">Registered Applicants</div>
            </div>
        </div>
        <div class="stat-card red">
            <div class="sc-icon">💳</div>
            <div class="sc-body">
                <div class="sc-number"><?= number_format($stats['payments_pending']) ?></div>
                <div class="sc-label">Payments Pending</div>
            </div>
        </div>
        <div class="stat-card teal">
            <div class="sc-icon">📋</div>
            <div class="sc-body">
                <div class="sc-number"><?= number_format($stats['shortlisted']) ?></div>
                <div class="sc-label">Shortlisted</div>
            </div>
        </div>
    </div>

    <!-- By College + Recent Applications -->
    <div class="admin-two-col">

        <!-- By College -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Applications by College</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr><th>College</th><th>Total</th><th>Admitted</th><th>Pending</th><th>Rate</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($byCollege as $col): ?>
                    <tr>
                        <td><strong><?= e($col['code']) ?></strong><br><small><?= e($col['name']) ?></small></td>
                        <td><?= number_format($col['total']) ?></td>
                        <td><span style="color:#1a6b4a;font-weight:700"><?= number_format($col['admitted']) ?></span></td>
                        <td><span style="color:#c9922a;font-weight:700"><?= number_format($col['pending']) ?></span></td>
                        <td>
                            <?php $rate = $col['total'] > 0 ? round(($col['admitted']/$col['total'])*100) : 0; ?>
                            <div class="mini-bar-wrap">
                                <div class="mini-bar" style="width:<?= $rate ?>%"></div>
                            </div>
                            <small><?= $rate ?>%</small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Payments Pending -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Payments Awaiting Verification</h3>
                <a href="<?= BASE_URL ?>/admin/applicants.php?filter=payment" class="btn btn-sm btn-outline">View All</a>
            </div>
            <?php if ($pendingPay): ?>
            <table class="admin-table">
                <thead><tr><th>Applicant</th><th>Type</th><th>Amount</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($pendingPay as $pay): ?>
                    <tr>
                        <td>
                            <strong><?= e($pay['surname'].', '.$pay['firstname']) ?></strong><br>
                            <small><?= e($pay['ref_number']) ?></small>
                        </td>
                        <td><?= e($pay['payment_type']) ?></td>
                        <td><strong><?= formatMoney($pay['amount']) ?></strong></td>
                        <td><?= formatDate($pay['created_at']) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/review.php?payment=<?= $pay['id'] ?>"
                               class="btn btn-sm btn-primary">Verify</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="admin-empty">✅ No pending payments.</div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Recent Applications -->
    <div class="admin-card mt-3">
        <div class="admin-card-header">
            <h3>Recent Applications</h3>
            <a href="<?= BASE_URL ?>/admin/applicants.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ref No.</th>
                        <th>Applicant</th>
                        <th>College</th>
                        <th>Programme</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $app): ?>
                    <tr>
                        <td><code><?= e($app['ref_number']) ?></code></td>
                        <td><?= e($app['surname'].', '.$app['firstname']) ?></td>
                        <td><span class="college-tag"><?= e($app['college_code']) ?></span></td>
                        <td><?= e($app['programme_name']) ?></td>
                        <td><?= statusBadge($app['status']) ?></td>
                        <td><?= formatDate($app['created_at']) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/review.php?id=<?= $app['id'] ?>"
                               class="btn btn-sm btn-outline">Review</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once dirname(__DIR__) . '/admin/partials/admin-footer.php'; ?>
