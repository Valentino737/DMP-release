<?php
/**
 * Panel moderátora - hlavní stránka
 * 
 * Přehled statistik fóra a odkazy na správu příspěvků,
 * komentářů, uživatelů a moderátorů. Pro adminy a moderátory.
 */
session_start();
require_once(__DIR__ . '/../../db/connection.php');
if (!isset($_SESSION['user_id']) || !in_array(($_SESSION['roleId'] ?? 1), [2, 3])) {
    header('Location: /dmp/public/login.php');
    exit;
}

$isAdmin = $_SESSION['roleId'] === 2;
$isModerator = $_SESSION['roleId'] === 3;
$username = $_SESSION['username'] ?? 'User';

// Statistiky fóra
try {
    // Celkový počet příspěvků
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM forum_posts WHERE isVisible = TRUE');
    $stmt->execute();
    $totalPosts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Celkový počet komentářů
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM forum_comments WHERE isVisible = TRUE');
    $stmt->execute();
    $totalComments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Počet čekajících hlášení
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM forum_reports WHERE status = "pending"');
    $stmt->execute();
    $pendingReports = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Celkový počet moderátorů
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM users WHERE roleId = 3');
    $stmt->execute();
    $totalModerators = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

} catch (Exception $e) {
    error_log("Moderator dashboard stats error: " . $e->getMessage());
}

?>

<?php include_once('../../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Panel moderátora - PC Konfigurátor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        .moderator-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            border-left: 5px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0;
            color: #667eea;
            font-size: 2.5em;
            font-weight: bold;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 0.95em;
        }
        .action-link {
            display: block;
            padding: 15px;
            background: #f9fafb;
            border: 1px solid #DEE5E5;
            border-radius: 8px;
            margin-bottom: 12px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .action-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .action-link strong {
            display: block;
            margin-bottom: 5px;
        }
        .action-link small {
            display: block;
            opacity: 0.8;
        }
    </style>
</head>
<body>

<?php include_once('../../includes/navbar.php'); ?>

<div class="container py-5">
    <div class="moderator-card">
        <h1>🛡️ Panel moderátora</h1>
        <p class="mb-0">Vítáme vás, <strong><?= htmlspecialchars($username) ?></strong>! 
            <?= $isAdmin ? '(Administrátor)' : '(Moderátor)' ?></p>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <h3><?= $totalPosts ?></h3>
                <p>Příspěvky fóra</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <h3><?= $totalComments ?></h3>
                <p>Komentáře</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <h3><?= $pendingReports ?></h3>
                <p>Čekající hlášení</p>
            </div>
        </div>
        <?php if ($isAdmin): ?>
        <div class="col-md-3">
            <div class="stat-box">
                <h3><?= $totalModerators ?></h3>
                <p>Moderátoři</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h4 class="mb-3">📋 Nástroje moderátora</h4>
            
            <a href="/dmp/public/admin/forum_reports.php" class="action-link">
                <strong>🚨 Hlášení fóra</strong>
                <small>Zobrazit a zpracovat hlášení od uživatelů</small>
            </a>

            <a href="/dmp/public/moderator/manage_posts.php" class="action-link">
                <strong>✏️ Správa příspěvků</strong>
                <small>Zobrazit, upravit nebo smazat příspěvky</small>
            </a>

            <a href="/dmp/public/moderator/manage_comments.php" class="action-link">
                <strong>💬 Správa komentářů</strong>
                <small>Prohlínout a moderovat komentáře</small>
            </a>

            <a href="/dmp/public/moderator/manage_banned_users.php" class="action-link">
                <strong>🚫 Správa zablokovaných</strong>
                <small>Zablokovat nebo odblokovat uživatele</small>
            </a>
        </div>

        <div class="col-md-6">
            <h4 class="mb-3">⚙️ Nástroje administrátora</h4>
            
            <?php if ($isAdmin): ?>
                <a href="/dmp/public/moderator/manage_moderators.php" class="action-link">
                    <strong>👥 Správa moderátorů</strong>
                    <small>Povyšovat nebo degradovat moderátory</small>
                </a>

                <a href="/dmp/public/admin/index.php" class="action-link">
                    <strong>🔧 Plný admin panel</strong>
                    <small>Správa komponent, uživatelů a nastavení</small>
                </a>

                <div class="alert alert-info mt-3">
                    <strong>Přístup administrátora:</strong> Máte plný přístup ke všem funkcím včetně správy komponent, uživatelů a přidělování moderátorů.
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>Oprávnění moderátora:</strong> Můžete prohlížet a moderovat obsah fóra, blokovat uživatele, zpracovávat hlášení a mazat nevhodné příspěvky nebo komentáře. Další administrátorské funkce jsou vyhrazeny pro administrátory.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-5">

    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-3">ℹ️ Vaše povinnosti</h4>
            <div class="card">
                <div class="card-body">
                    <ul>
                        <li><strong>Sledovat kvalitu fóra:</strong> Prohlížet příspěvky a komentáře na nevhodný obsah</li>
                        <li><strong>Zpracovávat hlášení:</strong> Vyšetřovat hlášení uživatelů a přijímat vhodná opatření</li>
                        <li><strong>Zajišťovat kvalitu:</strong> Mazat příspěvky/komentáře, které porušují pravidla komunity</li>
                        <li><strong>Vést komunitu:</strong> Pomáhat udržovat pozitivní a produktivní prostředí fóra</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
