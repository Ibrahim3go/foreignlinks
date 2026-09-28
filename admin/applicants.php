<?php
// ============================================================
//  Foreign Links Campus – Admin: All Applications
//  /admin/applicants.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

// ── Filters ──────────────────────────────────────────────────
$filterStatus  = clean($_GET['status']   ?? '');
$filterCollege = clean($_GET['college']  ?? '');
$filterSearch  = clean($_GET['search']   ?? '');
$filterSession = clean($_GET['session']  ?? '');

// Build WHERE clause
$where  = ['1=1'];
$params = [];

if ($filterStatus) {
    $where[]  = 'a.status = ?';
    $params[] = $filterStatus;
}
if ($filterCollege) {
    $where[]  = 'c.code = ?';
    $params[] = $filterCollege;
}
if ($filterSearch) {
    $where[]  = '(ap.ref_number LIKE ? OR ap.surname LIKE ? OR ap.firstname LIKE ? OR ap.email LIKE ?)';
    $s = '%' . $filterSearch . '%';
    $params = array_merge($params, [$s,$s,$s,$s]);
}
if ($filterSession) {
    $where[]  = 's.session = ?';
    $params[] = $filterSession;
}

// Admin restricted to own college?
if (!empty($_SESSION['admin_college'])) {
    $where[]  = 'a.college_id = ?';
    $params[] = (int)$_SESSION['admin_college'];
}

$whereSQL = implode(' AND ', $where);

// Pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN applicants ap  ON a.applicant_id  = ap.id
    JOIN programmes p   ON a.programme_id  = p.id
    JOIN colleges c     ON a.college_id    = c.id
    JOIN academic_sessions s ON a.session_id = s.id
    WHERE {$whereSQL}
");
$countStmt->execute($params);
$total   = (int)$countStmt->fetchColumn();
$perPage = 25;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$pages   = (int)ceil($total / $perPage);

// Fetch
$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.created_at, a.submitted_at,
           a.step_submitted, a.form_fee_paid, a.acceptance_paid,
           ap.ref_number, ap.surname, ap.firstname, ap.email, ap.phone,
           ap.passport_photo,
           p.name AS programme_name, p.qualification,
           c.name AS college_name, c.code AS college_code,
           s.session AS academic_session
    FROM   applications a
    JOIN   applicants ap  ON a.applicant_id  = ap.id
    JOIN   programmes p   ON a.programme_id  = p.id
    JOIN   colleges c     ON a.college_id    = c.id
    JOIN   academic_sessions s ON a.session_id = s.id
    WHERE  {$whereSQL}
    ORDER  BY a.created_at DESC
    LIMIT  {$perPage} OFFSET {$offset}
");
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Load filter options
$colleges  = getColleges($pdo);
$sessions  = $pdo->query("SELECT * FROM academic_sessions ORDER BY id DESC")->fetchAll();
$statuses  = ['Pending','Under Review','Shortlisted','Admitted','Rejected','Deferred'];

$pageTitle = 'All Applications';
require_once dirname(__DIR__) . '/admin/partials/admin-header.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <h1>Applications</h1>
            <p><?= number_format($total) ?> total records found</p>
        </div>
        <div class="admin-page-actions">
            <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-outline btn-sm">📥 Export</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-card filter-card">
        <form method="GET" action="" class="filter-form">
            <input type="text" name="search" class="form-control"
                   placeholder="Search by name, ref, email..."
                   value="<?= e($filterSearch) ?>">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                <option value="<?= $st ?>" <?= $filterStatus===$st?'selected':'' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
            <select name="college" class="form-control">
                <option value="">All Colleges</option>
                <?php foreach ($colleges as $col): ?>
                <option value="<?= e($col['code']) ?>" <?= $filterCollege===$col['code']?'selected':'' ?>><?= e($col['code']) ?> – <?= e($col['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="session" class="form-control">
                <option value="">All Sessions</option>
                <?php foreach ($sessions as $sess): ?>
                <option value="<?= e($sess['session']) ?>" <?= $filterSession===$sess['session']?'selected':'' ?>><?= e($sess['session']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= BASE_URL ?>/admin/applicants.php" class="btn btn-outline">Reset</a>
        </form>
    </div>

    <!-- Status Quick Filters -->
    <div class="quick-status-tabs">
        <a href="?" class="qst <?= !$filterStatus?'active':'' ?>">All (<?= $total ?>)</a>
        <?php foreach ($statuses as $st):
            $cnt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE status=?");
            $cnt->execute([$st]);
        ?>
        <a href="?status=<?= urlencode($st) ?>"
           class="qst qst-<?= strtolower(str_replace(' ','-',$st)) ?> <?= $filterStatus===$st?'active':'' ?>">
            <?= $st ?> (<?= $cnt->fetchColumn() ?>)
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                        <th>Ref No.</th>
                        <th>Applicant</th>
                        <th>College</th>
                        <th>Programme</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($applications): ?>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><input type="checkbox" class="row-check" value="<?= $app['id'] ?>"></td>
                        <td><code><?= e($app['ref_number']) ?></code></td>
                        <td>
                            <div class="applicant-cell">
                                <?php if ($app['passport_photo']): ?>
                                <img src="<?= UPLOAD_URL . e($app['passport_photo']) ?>"
                                     class="table-avatar" alt="">
                                <?php else: ?>
                                <div class="table-avatar-ph">
                                    <?= strtoupper(substr($app['firstname'],0,1).substr($app['surname'],0,1)) ?>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?= e($app['surname'].', '.$app['firstname']) ?></strong><br>
                                    <small><?= e($app['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="college-tag"><?= e($app['college_code']) ?></span></td>
                        <td>
                            <?= e($app['programme_name']) ?><br>
                            <small><?= e($app['qualification']) ?></small>
                        </td>
                        <td><?= statusBadge($app['status']) ?></td>
                        <td>
                            <?php if ($app['form_fee_paid']): ?>
                            <span class="pay-dot paid" title="App fee paid">●</span>
                            <?php else: ?>
                            <span class="pay-dot unpaid" title="App fee unpaid">●</span>
                            <?php endif; ?>
                            <?php if ($app['acceptance_paid']): ?>
                            <span class="pay-dot paid" title="Acceptance fee paid">●</span>
                            <?php else: ?>
                            <span class="pay-dot unpaid" title="Acceptance unpaid">●</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $app['step_submitted']
                                ? '<span style="color:#1a6b4a;font-weight:600">✓ Yes</span>'
                                : '<span style="color:#c9922a">Draft</span>' ?>
                            <?php if ($app['submitted_at']): ?>
                            <br><small><?= formatDate($app['submitted_at']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/review.php?id=<?= $app['id'] ?>"
                               class="btn btn-sm btn-primary">Review</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="9" class="admin-empty">No applications found matching your filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="admin-pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>"
               class="btn btn-sm btn-outline">← Prev</a>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $pages ?></span>
            <?php if ($page < $pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>"
               class="btn btn-sm btn-outline">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
function toggleAll(master) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
}
</script>

<?php require_once dirname(__DIR__) . '/admin/partials/admin-footer.php'; ?>
