<?php
/**
 * Pomocné funkce pro CSRF tokeny
 * 
 * Použití:
 *   1. Nejprve zavolejte session_start()
 *   2. Ve formulářích: echo csrf_field() pro výpis skrytého inputu
 *   3. V POST handlerech: if (!csrf_validate()) { $errors[] = "Neplatný požadavek."; }
 */

/**
 * Vygenerování nebo získání CSRF tokenu pro aktuální session
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Výpis skrytého input pole s CSRF tokenem
 */
function csrf_field($name = 'csrf_token') {
    $token = csrf_token();
    return sprintf(
        '<input type="hidden" name="%s" value="%s">',
        htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
    );
}

/**
 * Validace CSRF tokenu z POST požadavku
 * Vrací true pokud je platný, jinak false
 */
function csrf_validate($name = 'csrf_token') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }
    
    $token = $_POST[$name] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    
    // Použití hash_equals pro prevenci časových útoků
    return !empty($expected) && hash_equals($expected, $token);
}
