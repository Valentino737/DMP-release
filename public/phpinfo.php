<?php
session_start();

// Omezí phpinfo pouze pro přihlášené adminy
if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) {
    http_response_code(403);
    echo 'Přístup odepřen';
    exit;
}

phpinfo();
?>