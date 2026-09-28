<?php
// ============================================================
//  Foreign Links Campus – AJAX: Get Programmes by College
//  /process/get-programmes.php?college_id=X
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$collegeId = (int)($_GET['college_id'] ?? 0);
if (!$collegeId) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.qualification,
           p.duration_years, p.application_fee,
           d.name AS department
    FROM   programmes p
    JOIN   departments d ON p.department_id = d.id
    WHERE  p.college_id = ? AND p.is_active = 1
    ORDER  BY d.name, p.name
");
$stmt->execute([$collegeId]);
echo json_encode($stmt->fetchAll());
