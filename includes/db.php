<?php
// ============================================================
//  Foreign Links Campus – Database Connection
//  /includes/db.php
// ============================================================

require_once dirname(__DIR__) . '/config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname='    . DB_NAME
         . ';charset='   . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    // In production, log this — never expose to user
    error_log('DB Connection Failed: ' . $e->getMessage());
    die(json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed. Please try again later.'
    ]));
}
