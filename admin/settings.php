<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $fields = ['site_name','site_tagline','current_session','admission_open',
               'contact_phone','contact_email','bank_name','account_name',
               'account_number','application_fee_coe','application_fee_cht',
               'application_fee_son','application_fee_adp','application_fee_ful',
               'provost_name','smtp_host','smtp_port','smtp_user','smtp_pass'];
    $upd = $pdo->prepare("UPDATE site_settings SET setting_value=? WHERE setting_key=?");
    foreach ($fields as $k) {
        if (isset($_POST[$k])) $upd->execute([clean($_POST[$k]), $k]);
    }
    auditLog($pdo,'Admin',$_SESSION['admin_id'],'Settings Update','Site settings updated');
    setFlash('success','Settings saved successfully.');
    header('Location: ' . BASE_URL . '/admin/settings.php'); exit;
}

$settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
foreach ($settingsStmt->fetchAll() as $r) $settings[$r['setting_key']] = $r['setting_value'];

$pageTitle = 'Settings';
require_once dirname(__DIR__) . '/admin/partials/admin-header.php';
?>
<div class="admin-content">
    <div class="admin-page-header"><div><h1>Site Settings</h1><p>Manage portal configuration</p></div></div>
    <?= getFlash() ?>
    <form method="POST" action="">
        <?= csrfField() ?>
        <div class="admin-two-col">
            <div class="admin-card">
                <div class="admin-card-header"><h3>General</h3></div>
                <?php foreach ([
                    'site_name'=>'Site Name','site_tagline'=>'Tagline',
                    'current_session'=>'Current Session','provost_name'=>'Provost Name',
                    'contact_phone'=>'Contact Phone','contact_email'=>'Contact Email',
                ] as $k => $label): ?>
                <div class="form-group">
                    <label class="form-label"><?= $label ?></label>
                    <input type="text" name="<?= $k ?>" class="form-control"
                           value="<?= e($settings[$k] ?? '') ?>">
                </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <label class="form-label">Admission Open</label>
                    <select name="admission_open" class="form-control">
                        <option value="1" <?= ($settings['admission_open']??'1')==='1'?'selected':'' ?>>Open</option>
                        <option value="0" <?= ($settings['admission_open']??'1')==='0'?'selected':'' ?>>Closed</option>
                    </select>
                </div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header"><h3>Bank &amp; Fees</h3></div>
                <?php foreach ([
                    'bank_name'=>'Bank Name','account_name'=>'Account Name',
                    'account_number'=>'Account Number',
                ] as $k => $label): ?>
                <div class="form-group">
                    <label class="form-label"><?= $label ?></label>
                    <input type="text" name="<?= $k ?>" class="form-control"
                           value="<?= e($settings[$k] ?? '') ?>">
                </div>
                <?php endforeach; ?>
                <div class="admin-card-header" style="margin-top:16px"><h3>Application Fees (₦)</h3></div>
                <?php foreach ([
                    'application_fee_coe'=>'COE – College of Education',
                    'application_fee_cht'=>'CHT – Health Technology',
                    'application_fee_son'=>'SON – School of Nursing',
                    'application_fee_adp'=>'ADP – Affiliate Degree',
                    'application_fee_ful'=>'FUL – Foreign University',
                ] as $k => $label): ?>
                <div class="form-group">
                    <label class="form-label"><?= $label ?></label>
                    <input type="number" name="<?= $k ?>" class="form-control"
                           value="<?= e($settings[$k] ?? '5000') ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-card-header"><h3>Email / SMTP</h3></div>
            <div class="form-row">
                <?php foreach (['smtp_host'=>'SMTP Host','smtp_port'=>'SMTP Port','smtp_user'=>'SMTP Username','smtp_pass'=>'SMTP Password'] as $k=>$label): ?>
                <div class="form-group">
                    <label class="form-label"><?= $label ?></label>
                    <input type="text" name="<?= $k ?>" class="form-control"
                           value="<?= e($settings[$k] ?? '') ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="margin-top:20px">
            <button type="submit" class="btn btn-primary btn-lg">💾 Save All Settings</button>
        </div>
    </form>
</div>
<?php require_once dirname(__DIR__) . '/admin/partials/admin-footer.php'; ?>
