<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
logoutUser();
header('Location: ' . BASE_URL . '/login.php?msg=logged_out');
exit;
