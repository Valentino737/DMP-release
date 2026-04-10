<?php
/**
 * Stránka pro nastavení a konfiguraci emailového servisu, odkazuje na ní readme
 */

$configFile = __DIR__ . '/../config/email_config.php';
$envFile = __DIR__ . '/../../.env';

// Kontrola, jestli lze email servis nakonfigurovat
$errors = [];
$warnings = [];
$success = [];

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Konfigurátor - Nastavení</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f9fafb; padding: 20px; }
        .setup-container { max-width: 800px; margin: 0 auto; }
        .setup-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .status-item { display: flex; align-items: center; padding: 10px 0; }
        .status-icon { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 10px; }
        .status-ok { background: #28a745; }
        .status-error { background: #dc3545; }
        .status-warning { background: #ffc107; color: black; }
    </style>
</head>
<body>

<div class="setup-container">
    <div class="setup-card">
        <h1 class="mb-4">🔧 Konfigurátor – Nastavení</h1>

        <h3 class="mt-4">Krok 1: Instalace závislostí</h3>
        <p>Spusťte tento příkaz v kořenovém adresáři projektu:</p>
        <pre><code>composer install</code></pre>
        <p>Pokud nemáte nainstalovaný Composer, stáhněte jej z <a href="https://getcomposer.org/" target="_blank">https://getcomposer.org/</a></p>

        <h3 class="mt-4">Krok 2: Konfigurace e-mailu</h3>
        <p>Zkopírujte <code>.env.example</code> do <code>.env</code> a nastavte SMTP údaje:</p>
        <pre><code>cp .env.example .env</code></pre>
        <p>Poté upravte soubor <code>.env</code> s přihlašovacími údaji vašeho poskytovatele e-mailu.</p>

        <h3 class="mt-4">Proměnné prostředí</h3>
        <p>Aplikace rozpoznává následující proměnné prostředí:</p>
        <ul>
            <li><strong>MAIL_HOST</strong> – SMTP server (výchozí: smtp.gmail.com)</li>
            <li><strong>MAIL_PORT</strong> – SMTP port (výchozí: 587)</li>
            <li><strong>MAIL_USERNAME</strong> – SMTP uživatelské jméno / e-mail</li>
            <li><strong>MAIL_PASSWORD</strong> – SMTP heslo nebo heslo aplikace</li>
            <li><strong>MAIL_ENCRYPTION</strong> – tls nebo ssl (výchozí: tls)</li>
            <li><strong>MAIL_FROM_EMAIL</strong> – E-mailová adresa odesílatele</li>
            <li><strong>MAIL_DEBUG</strong> – Nastavte na 1 pro ladící výpis</li>
        </ul>

        <h3 class="mt-4">Systémové požadavky</h3>
        <div class="status-item">
            <div class="status-icon status-<?= version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'error' ?>">
                <?= version_compare(PHP_VERSION, '7.4.0', '>=') ? '✓' : '✗' ?>
            </div>
            <div>
                <strong>Verze PHP:</strong> <?= PHP_VERSION ?> (Vyžadováno: 7.4+)
            </div>
        </div>

        <div class="status-item">
            <div class="status-icon status-<?= extension_loaded('pdo') ? 'ok' : 'error' ?>">
                <?= extension_loaded('pdo') ? '✓' : '✗' ?>
            </div>
            <div>
                <strong>Rozšíření PDO:</strong> <?= extension_loaded('pdo') ? 'Nainstalováno' : 'Nenalezeno' ?>
            </div>
        </div>

        <div class="status-item">
            <div class="status-icon status-<?= extension_loaded('pdo_mysql') ? 'ok' : 'error' ?>">
                <?= extension_loaded('pdo_mysql') ? '✓' : '✗' ?>
            </div>
            <div>
                <strong>PDO MySQL:</strong> <?= extension_loaded('pdo_mysql') ? 'Nainstalováno' : 'Nenalezeno' ?>
            </div>
        </div>

        <div class="status-item">
            <div class="status-icon status-<?= file_exists(__DIR__ . '/../../vendor/autoload.php') ? 'ok' : 'warning' ?>">
                <?= file_exists(__DIR__ . '/../../vendor/autoload.php') ? '✓' : '⚠' ?>
            </div>
            <div>
                <strong>Composer Autoload:</strong> <?= file_exists(__DIR__ . '/../../vendor/autoload.php') ? 'Nalezen' : 'Nenalezen – spusťte: composer install' ?>
            </div>
        </div>

        <div class="status-item">
            <div class="status-icon status-<?= is_readable($configFile) ? 'ok' : 'warning' ?>">
                <?= is_readable($configFile) ? '✓' : '⚠' ?>
            </div>
            <div>
                <strong>Konfigurace e-mailu:</strong> <?= is_readable($configFile) ? 'Nalezena' : 'Nenalezena' ?>
            </div>
        </div>

        <h3 class="mt-4">Nasazení na školní server</h3>
        <ol>
            <li>Zkopírujte všechny soubory na školní server (kromě <code>.git</code> a <code>.env</code>)</li>
            <li>Spusťte <code>composer install</code> na školním serveru</li>
            <li>Importujte SQL soubor databáze</li>
            <li>Vytvořte soubor <code>.env</code> s SMTP údaji školního serveru</li>
            <li>Otestujte funkčnost e-mailů</li>
        </ol>

        <h3 class="mt-4">Řešení problémů</h3>
        <ul>
            <li><strong>E-maily se neodesílají:</strong> Zkontrolujte SMTP údaje v souboru <code>.env</code>. Pro podrobnosti o chybách nastavte MAIL_DEBUG=1.</li>
            <li><strong>Composer install selže:</strong> Ujistěte se, že PHP CLI je správně nakonfigurováno. Ověřte příkazem <code>php -v</code></li>
            <li><strong>Chyby databáze:</strong> Importujte SQL soubor v phpMyAdmin</li>
            <li><strong>Přístup odepřen:</strong> Ujistěte se, že webový server má oprávnění k zápisu do složky config/</li>
        </ul>

        <div class="mt-5 p-3 bg-info text-white rounded">
            <strong>Další kroky:</strong> Po nastavení souboru .env a spuštění composer install zaregistrujte nový účet v aplikaci a otestujte proces ověření e-mailu.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
