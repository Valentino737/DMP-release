<?php
/**
 * Emailová konfigurace pro projekt
 * 
 * Nakonfigurujte zde své SMTP nastavení. Můžete použít proměnné prostředí
 * pro citlivé informace, nebo je definovat přímo níže.
 * 
 * Pro použití nastavte před spuštěním projektu:
 *   - MAIL_HOST: hostname SMTP serveru (např. smtp.gmail.com)
 *   - MAIL_PORT: port SMTP (obvykle 587 pro TLS, 465 pro SSL)
 *   - MAIL_USERNAME: uživatelské jméno/email pro SMTP
 *   - MAIL_PASSWORD: SMTP heslo nebo aplikační heslo
 */

// Load .env souboru pokud není načteno
if (!function_exists('loadEnvFile')) {
    require_once __DIR__ . '/env_loader.php';
}

return [
    // SMTP config
    'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    'port' => (int)(getenv('MAIL_PORT') ?: 587),
    'username' => getenv('MAIL_USERNAME') ?: '',
    'password' => getenv('MAIL_PASSWORD') ?: '',
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls', // 'tls' or 'ssl'
    
    // Emailové adresy
    'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'noreply@dmp-configurator.local',
    'from_name' => 'DMP PC Configurator',
    
    // Nastavení pro PHPMailer
    'timeout' => 10, // SMTP timeout v sekundách
    'debug' => (bool)getenv('MAIL_DEBUG') ?: false, // Povolit pro ladění SMTP komunikace
];
?>
