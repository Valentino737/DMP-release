<?php
/**
 * Admin panel - hlavní stránka
 * 
 * Přehled administrace: statistiky komponent, počty záznamů
 * a odkazy na správu jednotlivých typů. Pouze pro adminy (roleId=2).
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';

// Kontrola admin přístupu
if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) {
    header("Location: /dmp/public/login.php");
    exit;
}

$username = $_SESSION['username'];

$componentTypes = [
    'cpu' => ['name' => 'Procesory (CPU)', 'icon' => '⚙️', 'url' => '/dmp/public/admin/manage_cpu.php'],
    'gpu' => ['name' => 'Grafické karty (GPU)', 'icon' => '🎮', 'url' => '/dmp/public/admin/manage_gpu.php'],
    'ram' => ['name' => 'Operační paměť (RAM)', 'icon' => '🧠', 'url' => '/dmp/public/admin/manage_ram.php'],
    'motherboard' => ['name' => 'Základní desky', 'icon' => '🖥️', 'url' => '/dmp/public/admin/manage_motherboard.php'],
    'psu' => ['name' => 'Zdroje (PSU)', 'icon' => '⚡', 'url' => '/dmp/public/admin/manage_psu.php'],
    'case' => ['name' => 'PC Skříně', 'icon' => '📦', 'url' => '/dmp/public/admin/manage_case.php'],
    'storage' => ['name' => 'Úložiště', 'icon' => '💾', 'url' => '/dmp/public/admin/manage_storage.php'],
];

// Get statistik
$stats = [];
foreach (array_keys($componentTypes) as $type) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM `$type`");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats[$type] = $result['count'];
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - DMP Konfigurátor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        html {
            background: linear-gradient(135deg, #f9fafb 0%, #e2e8e2 100%);
        }
        body {
            min-height: 100vh;
        }
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .admin-header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 4px solid #dc3545;
        }
        .admin-header h1 {
            margin: 0;
            color: #0A0908;
            font-size: 2rem;
            font-weight: 700;
        }
        .admin-header p {
            margin: 8px 0 0 0;
            color: #666;
        }
        .component-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 2rem;
        }
        @media (max-width: 1200px) {
            .component-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 768px) {
            .component-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .component-grid {
                grid-template-columns: 1fr;
            }
        }
        .component-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #618B4A;
            display: flex;
            flex-direction: column;
        }
        .component-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
            text-decoration: none;
        }
        .component-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }
        .component-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0A0908;
            margin-bottom: 8px;
        }
        .component-count {
            font-size: 0.9rem;
            color: #666;
            flex-grow: 1;
        }
        .component-count strong {
            color: #618B4A;
            font-size: 1.3rem;
        }
        .nav-top {
            background: white;
            border-bottom: 1px solid #ddd;
            padding: 12px 0;
            margin-bottom: 24px;
        }
        .nav-top a {
            color: #0A0908;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-right: 16px;
        }
        .nav-top a:hover {
            text-decoration: underline;
        }
        .logout-btn {
            color: #dc3545 !important;
        }
    </style>
</head>
<body>
    <div class="nav-top">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <a href="/dmp/public/index.php">← Zpět na web</a>
            <a href="/dmp/public/dashboard.php">Můj panel</a>
            <a href="/dmp/public/logout.php" class="logout-btn">Odhlásit se</a>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-header">
            <h1>🔐 Admin Panel</h1>
            <p>Přihlášen jako: <strong><?= htmlspecialchars($username) ?></strong></p>
        </div>

        <!-- Sekce nástrojů admina -->
        <h2 style="margin-bottom: 2rem; color: #0A0908; font-weight: 700;">🛡️ Nástroje moderace</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 2rem;">
            <!-- Hlášení z fóra -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 1.5rem; border-left: 4px solid #dc3545;">
                <h3 style="color: #0A0908; font-weight: 700; margin: 0 0 1rem 0;">📋 Hlášení z fóra</h3>
                <p style="color: #666; margin: 0 0 1rem 0;">Kontrola a zpracování hlášení od uživatelů.</p>
                <a href="/dmp/public/admin/forum_reports.php" class="btn btn-outline-danger">Zobrazit hlášení</a>
            </div>

            <!-- Správa příspěvků -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 1.5rem; border-left: 4px solid #007bff;">
                <h3 style="color: #0A0908; font-weight: 700; margin: 0 0 1rem 0;">✏️ Správa příspěvků</h3>
                <p style="color: #666; margin: 0 0 1rem 0;">Zobrazit, moderovat nebo smazat příspěvky na fóru.</p>
                <a href="/dmp/public/moderator/manage_posts.php" class="btn btn-outline-primary">Spravovat</a>
            </div>

            <!-- Správa komentářů -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 1.5rem; border-left: 4px solid #17a2b8;">
                <h3 style="color: #0A0908; font-weight: 700; margin: 0 0 1rem 0;">💬 Správa komentářů</h3>
                <p style="color: #666; margin: 0 0 1rem 0;">Kontrola a moderování komentářů uživatelů.</p>
                <a href="/dmp/public/moderator/manage_comments.php" class="btn btn-outline-info">Spravovat</a>
            </div>

            <!-- Zablokovaní uživatelé -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 1.5rem; border-left: 4px solid #6c757d;">
                <h3 style="color: #0A0908; font-weight: 700; margin: 0 0 1rem 0;">🚫 Zablokovaní uživatelé</h3>
                <p style="color: #666; margin: 0 0 1rem 0;">Zablokovat nebo odblokovat uživatele na fóru.</p>
                <a href="/dmp/public/moderator/manage_banned_users.php" class="btn btn-outline-secondary">Spravovat</a>
            </div>

            <!-- Správa moderátorů -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 1.5rem; border-left: 4px solid #ffc107;">
                <h3 style="color: #0A0908; font-weight: 700; margin: 0 0 1rem 0;">👥 Správa moderátorů</h3>
                <p style="color: #666; margin: 0 0 1rem 0;">Povýšit uživatele na moderátory, nebo je naopak sesadit.</p>
                <a href="/dmp/public/moderator/manage_moderators.php" class="btn btn-outline-warning">Spravovat</a>
            </div>

            <!-- Návrhy komponent -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 1.5rem; border-left: 4px solid #20c997;">
                <h3 style="color: #0A0908; font-weight: 700; margin: 0 0 1rem 0;">📤 Návrhy komponent</h3>
                <p style="color: #666; margin: 0 0 1rem 0;">Kontrola komponent navržených uživateli.</p>
                <a href="/dmp/public/admin/manage_component_submissions.php" class="btn btn-outline-success">Zkontrolovat</a>
            </div>
        </div>

        <h2 style="margin-bottom: 2rem; color: #0A0908; font-weight: 700;">Spravovat komponenty</h2>

        <div class="component-grid">
            <?php foreach ($componentTypes as $type => $info): ?>
                <a href="<?= $info['url'] ?>" class="component-card">
                    <div class="component-icon"><?= $info['icon'] ?></div>
                    <div class="component-name"><?= $info['name'] ?></div>
                    <div class="component-count">
                        <strong><?= $stats[$type] ?></strong> komponent
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

   <?php include __DIR__ . '/../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
