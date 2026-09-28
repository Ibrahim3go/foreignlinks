<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();

$export = clean($_GET['export'] ?? '');
$filterStatus  = clean($_GET['status']  ?? '');
$filterCollege = clean($_GET['college'] ?? '');

$where = ['1=1']; $params = [];
if ($filterStatus)  { $where[] = 'a.status=?'; $params[] = $filterStatus; }
if ($filterCollege) { $where[] = 'c.code=?';   $params[] = $filterCollege; }
$wsql = implode(' AND ', $where);

$rows = $pdo->prepare("
    SELECT ap.ref_number, ap.surname, ap.firstname, ap.email, ap.phone,
           ap.gender, ap.state_of_origin, ap.date_of_birth,
           p.name AS programme, p.qualification, c.code AS college,
           a.status, a.entry_mode, a.jamb_score,
           a.form_fee_paid, a.acceptance_paid,
           a.created_at, a.submitted_at
    FROM applications a
    JOIN applicants ap ON a.applicant_id=ap.id
    JOIN programmes p  ON a.programme_id=p.id
    JOIN colleges c    ON a.college_id=c.id
    WHERE $wsql ORDER BY a.created_at DESC
");
$rows->execute($params);
$data = $rows->fetchAll();

// CSV Export
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="foreignlinks_applications_'.date('Ymd').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['Ref No','Surname','Firstname','Email','Phone','Gender','State',
                  'DOB','Programme','Qual','College','Status','Entry Mode',
                  'JAMB Score','App Fee','Acceptance Fee','Registered','Submitted']);
    foreach ($data as $r) {
        fputcsv($out,[
            $r['ref_number'],$r['surname'],$r['firstname'],$r['email'],$r['phone'],
            $r['gender'],$r['state_of_origin'],$r['date_of_birth'],
            $r['programme'],$r['qualification'],$r['college'],$r['status'],
            $r['entry_mode'],$r['jamb_score'],
            $r['form_fee_paid']?'Paid':'Unpaid',
            $r['acceptance_paid']?'Paid':'Unpaid',
            $r['created_at'],$r['submitted_at']
        ]);
    }
    fclose($out); exit;
}

$colleges = getColleges($pdo);
$statuses = ['Pending','Under Review','Shortlisted','Admitted','Rejected','Deferred'];
$pageTitle = 'Reports & Export';
require_once dirname(__DIR__) . '/admin/partials/admin-header.php';
?>
<div class="admin-content">
    <div class="admin-page-header">
        <div><h1>Reports &amp; Export</h1><p><?= count($data) ?> records matching current filter</p></div>
        <div class="admin-page-actions">
            <a href="?<?= http_build_query(array_merge($_GET,['export'=>'csv'])) ?>"
               class="btn btn-primary">📥 Export CSV</a>
        </div>
    </div>
    <div class="admin-card filter-card">
        <form method="GET" class="filter-form">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <select name="college" class="form-control">
                <option value="">All Colleges</option>
                <?php foreach ($colleges as $col): ?>
                <option value="<?= e($col['code']) ?>" <?= $filterCollege===$col['code']?'selected':'' ?>><?= e($col['code']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-outline">Reset</a>
        </form>
    </div>
    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Ref</th><th>Name</th><th>College</th><th>Programme</th><th>Status</th><th>App Fee</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($data as $r): ?>
                    <tr>
                        <td><code><?= e($r['ref_number']) ?></code></td>
                        <td><?= e($r['surname'].', '.$r['firstname']) ?></td>
                        <td><span class="college-tag"><?= e($r['college']) ?></span></td>
                        <td><?= e($r['programme']) ?></td>
                        <td><?= statusBadge($r['status']) ?></td>
                        <td><?= $r['form_fee_paid']?'<span style="color:#1a6b4a">✓</span>':'<span style="color:#c9922a">✗</span>' ?></td>
                        <td><?= formatDate($r['created_at']) ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/review.php?id=<?= urlencode($r['ref_number']) ?>" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/admin/partials/admin-footer.php'; ?>
