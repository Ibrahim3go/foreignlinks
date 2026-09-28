<?php
// ============================================================
//  Foreign Links Campus – Document Upload Page
//  /documents.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireApplicantLogin();

$applicantId = (int)$_SESSION['applicant_id'];
$application = getActiveApplication($pdo, $applicantId);

if (!$application) {
    setFlash('info','Please complete your application form first.');
    header('Location: ' . BASE_URL . '/apply.php'); exit;
}

// Handle upload POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error','Invalid request token.'); header('Location: ' . BASE_URL . '/documents.php'); exit;
    }
    $docType = clean($_POST['document_type'] ?? '');
    if (!$docType) {
        setFlash('error','Please select a document type.'); header('Location: ' . BASE_URL . '/documents.php'); exit;
    }
    if (empty($_FILES['doc_file']['name'])) {
        setFlash('error','Please select a file to upload.'); header('Location: ' . BASE_URL . '/documents.php'); exit;
    }
    $upload = uploadFile($_FILES['doc_file'], 'documents', ALLOWED_DOC_TYPES);
    if (!$upload['success']) {
        setFlash('error', $upload['error']); header('Location: ' . BASE_URL . '/documents.php'); exit;
    }
    $pdo->prepare("INSERT INTO documents
        (application_id,document_type,file_name,file_path,file_size,mime_type)
        VALUES (?,?,?,?,?,?)")
        ->execute([$application['id'],$docType,$upload['original'],
                   $upload['path'],$upload['size'],$upload['mime_type']]);

    auditLog($pdo,'Applicant',$applicantId,'Document Upload',"{$docType}: {$upload['original']}");
    setFlash('success',$docType . ' uploaded successfully.');
    header('Location: ' . BASE_URL . '/documents.php'); exit;
}

// Load existing documents
$docStmt = $pdo->prepare("SELECT * FROM documents WHERE application_id=? ORDER BY document_type, uploaded_at DESC");
$docStmt->execute([$application['id']]);
$documents = $docStmt->fetchAll();

// Required docs based on college
$reqDocs = [
    'COE' => ['Passport Photo','Birth Certificate','WAEC/NECO Result','JAMB Result','LGA Identification'],
    'CHT' => ['Passport Photo','Birth Certificate','WAEC/NECO Result','JAMB Result','Medical Certificate'],
    'SON' => ['Passport Photo','Birth Certificate','WAEC/NECO Result','Medical Certificate','Attestation Letter'],
    'ADP' => ['Passport Photo','Birth Certificate','WAEC/NECO Result','JAMB Result','O Level Certificate'],
    'FUL' => ['Passport Photo','Birth Certificate','WAEC/NECO Result','International Passport','Visa Document'],
];
$required = $reqDocs[$application['college_code']] ?? $reqDocs['COE'];

// All document types for dropdown
$allDocTypes = [
    'Passport Photo','Birth Certificate','WAEC/NECO Result',
    'JAMB Result','O Level Certificate','First Degree Certificate',
    'HND Certificate','NYSC Certificate','Medical Certificate',
    'LGA Identification','Attestation Letter','International Passport',
    'Visa Document','Transfer Letter','Payment Proof','Other',
];

// Uploaded types (for checking completeness)
$uploadedTypes = array_column($documents, 'document_type');

$pageTitle = 'Upload Documents';
$bodyClass = 'page-documents';
require_once 'includes/header.php';
?>

<section class="inner-section">
<div class="container">

    <div class="inner-header">
        <a href="<?= BASE_URL ?>/dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Document Upload</h1>
        <p><?= e($application['programme_name']) ?> — <?= e($application['college_name']) ?></p>
    </div>

    <?= getFlash() ?>

    <div class="payment-grid">

        <!-- LEFT: Upload Form -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3>Upload a Document</h3>
            </div>

            <div class="doc-requirements">
                <h4>📋 Required Documents for <?= e($application['college_code']) ?></h4>
                <ul class="req-list">
                    <?php foreach ($required as $req): ?>
                    <li class="req-item <?= in_array($req, $uploadedTypes) ? 'uploaded' : '' ?>">
                        <span><?= in_array($req, $uploadedTypes) ? '✓' : '○' ?></span>
                        <?= e($req) ?>
                        <?php if (in_array($req, $uploadedTypes)): ?>
                        <span class="req-done">Uploaded</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <form method="POST" action="" enctype="multipart/form-data"
                  class="apply-form" novalidate>
                <?= csrfField() ?>

                <div class="form-group">
                    <label class="form-label">Document Type <span class="required">*</span></label>
                    <select name="document_type" class="form-control" required>
                        <option value="">— Select Document Type —</option>
                        <?php foreach ($allDocTypes as $dt): ?>
                        <option value="<?= $dt ?>"><?= $dt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Select File <span class="required">*</span></label>
                    <div class="upload-box" onclick="document.getElementById('doc_file').click()">
                        <div class="upload-preview" id="docPreview">
                            <span class="upload-icon">📁</span>
                            <span>Click to select file</span>
                            <small>JPG, PNG or PDF · Max 5MB</small>
                        </div>
                        <input type="file" id="doc_file" name="doc_file"
                               accept="image/jpeg,image/png,application/pdf"
                               style="display:none"
                               onchange="previewDoc(this)">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        📤 Upload Document
                    </button>
                    <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline">
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>

        <!-- RIGHT: Uploaded Documents -->
        <div class="dash-sidebar">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Uploaded Documents</h3>
                    <span class="doc-count"><?= count($documents) ?></span>
                </div>

                <?php if ($documents): ?>
                <div class="uploaded-doc-list">
                    <?php foreach ($documents as $doc): ?>
                    <div class="uploaded-doc-item">
                        <div class="udi-icon">
                            <?= in_array($doc['mime_type'],['image/jpeg','image/png','image/jpg']) ? '🖼' : '📄' ?>
                        </div>
                        <div class="udi-info">
                            <strong><?= e($doc['document_type']) ?></strong>
                            <small><?= e($doc['file_name']) ?></small>
                            <small><?= formatDateTime($doc['uploaded_at']) ?></small>
                        </div>
                        <div class="udi-status">
                            <?php if ($doc['is_verified']): ?>
                            <span class="doc-status verified">✓ Verified</span>
                            <?php else: ?>
                            <span class="doc-status pending">⏳ Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <span>📂</span>
                    <p>No documents uploaded yet.</p>
                    <small>Upload all required documents to complete your application.</small>
                </div>
                <?php endif; ?>
            </div>

            <!-- Upload Guidelines -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Upload Guidelines</h3></div>
                <ul class="guidelines-list">
                    <li>✅ Accepted formats: JPG, PNG, PDF</li>
                    <li>✅ Maximum file size: 5MB per file</li>
                    <li>✅ Documents must be clear and legible</li>
                    <li>✅ Passport photo must have white background</li>
                    <li>✅ Certificates must show your full name</li>
                    <li>⚠️ Forged documents lead to disqualification</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</section>

<script>
function previewDoc(input) {
    const preview = document.getElementById('docPreview');
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.type === 'application/pdf') {
        preview.innerHTML = `<span class="upload-icon">📄</span><strong>${file.name}</strong><small>${(file.size/1024).toFixed(0)} KB</small>`;
    } else {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" class="preview-img" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
