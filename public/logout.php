<?php
/**
 * Odhlášení uživatele
 * 
 * Vymaže session data, smaže session cookie a zničí session.
 * Přesměruje na přihlašovací stránku.
 */
session_start();

// Vymazání všech session dat
$_SESSION = [];

// Smazání session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

header("Location: login.php");
exit;
