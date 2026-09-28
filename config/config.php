<?php
// ============================================================
//  Foreign Links Campus – Main Configuration File
//  /config/config.php
// ============================================================

// ── Error Reporting (set to 0 in production) ────────────────
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── Session Start ────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Database Credentials ─────────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'foreignlinks_db');
define('DB_USER',     'root');        // XAMPP default
define('DB_PASS',     '');            // XAMPP default (empty)
define('DB_CHARSET',  'utf8mb4');

// ── Site Information ─────────────────────────────────────────
define('SITE_NAME',      'Foreign Links Campus');
define('SITE_TAGLINE',   'Excellence in Education – Moro, Ife-North, Osun State');
define('SITE_EMAIL',     'info@foreignlinks.edu.ng');
define('SITE_PHONE',     '+234 803 3705 261');
define('SITE_ADDRESS',   'Osogbo Road, Moro, Ife-North LGA, Osun State, Nigeria');
define('SITE_SESSION',   '2024/2025');
define('PROVOST_NAME',   'Dr. Nsikak Akpan');

// ── URL & Path Configuration ─────────────────────────────────
// Base URL — change if your folder name differs
define('BASE_URL',   'http://localhost/foreignlinks');
define('BASE_PATH',  dirname(__DIR__));   // absolute path to /foreignlinks

// ── Upload Paths ─────────────────────────────────────────────
define('UPLOAD_PATH',    BASE_PATH . '/uploads/');
define('UPLOAD_URL',     BASE_URL  . '/uploads/');
define('MAX_FILE_SIZE',  5 * 1024 * 1024);   // 5 MB

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_DOC_TYPES',   [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/jpg'
]);

// ── Pagination ───────────────────────────────────────────────
define('RECORDS_PER_PAGE', 20);

// ── Application Fee by College Code ──────────────────────────
define('FEES', [
    'COE' => ['application' => 5000,  'acceptance' => 10000],
    'CHT' => ['application' => 5000,  'acceptance' => 10000],
    'SON' => ['application' => 7000,  'acceptance' => 15000],
    'ADP' => ['application' => 8000,  'acceptance' => 20000],
    'FUL' => ['application' => 10000, 'acceptance' => 25000],
]);

// ── Bank Details ─────────────────────────────────────────────
define('BANK_NAME',       'First Bank of Nigeria');
define('ACCOUNT_NAME',    'Foreign Links Campus');
define('ACCOUNT_NUMBER',  '0000000000');   // replace with real account

// ── Mail (SMTP via PHPMailer) ────────────────────────────────
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     '');   // your Gmail / SMTP address
define('SMTP_PASS',     '');   // app password
define('SMTP_FROM',     'noreply@foreignlinks.edu.ng');
define('SMTP_FROM_NAME', SITE_NAME);

// ── Reference Number Prefix ──────────────────────────────────
define('REF_PREFIX', 'FLC');

// ── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('Africa/Lagos');

// ── Admission Open/Closed ────────────────────────────────────
define('ADMISSION_OPEN', true);   // set false to close portal
