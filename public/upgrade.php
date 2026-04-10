<?php
/**
 * Stránka předplatného
 * 
 * Zobrazuje dostupné tarify (Free, Pro, Premium)
 * a umožňuje uživateli upgradovat nebo zrušit předplatné.
 * Vyžaduje přihlášení.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';

// Přesměrování nepřihlášeného uživatele
if (!isset($_SESSION['user_id'])) {
    header("Location: /dmp/public/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT subscription, createdAt FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found, redirect to login
    header("Location: /dmp/public/login.php");
    exit;
}

$currentSubscription = (int)($user['subscription'] ?? 1);

// Validate subscription is within valid range (1-3)
if (!in_array($currentSubscription, [1, 2, 3])) {
    $currentSubscription = 1;
}

$tiers = [
    1 => ['name' => 'Zdarma', 'price' => '0 Kč', 'buildsLabel' => 'Až 2 sestavy', 'features' => ['Až 2 uložené sestavy', 'Komunita na fóru']],
    2 => ['name' => 'Pro', 'price' => '99 Kč/měsíc', 'buildsLabel' => 'Až 6 sestav', 'features' => ['Až 6 uložených sestav',  'Prioritní schválení návrhů komponent']],
    3 => ['name' => 'Premium', 'price' => '199 Kč/měsíc', 'buildsLabel' => 'Neomezené sestavy', 'features' => ['Neomezené uložené sestavy',  'Prioritní schválení návrhů komponent']]
];

$cancellationMessage = '';
if (isset($_GET['cancelled']) && !empty($_SESSION['subscription_cancelled'])) {
    $cancellationMessage = '✅ Vaše předplatné bylo úspěšně zrušeno. Vrátili jste se na bezplatný tarif.';
    unset($_SESSION['subscription_cancelled']);
}

$upgradeMessage = '';
$selectedTier = (int)($_GET['tier'] ?? 0);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upgrade Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        body { background: #f9fafb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 0; text-align: center; }
        .pricing-card { border: 2px solid #e0e0e0; border-radius: 12px; padding: 2rem; margin: 1rem 0; transition: all 0.3s ease; display: flex; flex-direction: column; height: 100%; }
        .pricing-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .pricing-card.current { border-color: #28a745; background: #f0f8f4; }
        .pricing-card.highlight { border: 3px solid #667eea; background: #f8f9ff; }
        .price { font-size: 2rem; font-weight: bold; color: #667eea; margin: 1rem 0; }
        .features { list-style: none; padding: 0; flex-grow: 1; }
        .features li { padding: 0.5rem 0; color: #555; }
        .features li:before { content: "✓ "; color: #28a745; font-weight: bold; margin-right: 0.5rem; }
        .btn-upgrade { background: #667eea; color: white; border: none; padding: 0.75rem 2rem; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        .btn-upgrade:hover { background: #764ba2; }
        .btn-current { background: #6c757d; color: white; cursor: default; }
        .upgrade-container { max-width: 1200px; margin: 3rem auto; }
        .section { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; }
        .badge-current { background: #28a745; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="hero">
        <h1>🚀 Upgradujte svůj plán</h1>
        <p>Odemkněte více sestav a pokročilé funkce</p>
    </div>
    
    <div class="upgrade-container">
        <?php if ($cancellationMessage): ?>
            <div class="section" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
                <h4><?= $cancellationMessage ?></h4>
                <p style="margin: 0; font-size: 0.95rem;">Máte teď přístup k <strong>2 volným sestavám</strong>. Kdykoliv se můžete vrátit na vyšší plán.</p>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <h2 style="margin-bottom: 2rem; text-align: center;">Váš aktuální plán: <span class="badge-current"><?= htmlspecialchars($tiers[$currentSubscription]['name']) ?></span></h2>
            
            <div class="row">
                <?php foreach ($tiers as $tierId => $tier): ?>
                    <div class="col-md-4">
                        <div class="pricing-card <?= $tierId === $currentSubscription ? 'current' : '' ?> <?= $tierId > $currentSubscription ? 'highlight' : '' ?>">
                            <h3><?= htmlspecialchars($tier['name']) ?></h3>
                            <div class="price"><?= htmlspecialchars($tier['price']) ?></div>
                            <p style="color: #667eea; font-weight: bold;"><?= htmlspecialchars($tier['buildsLabel']) ?></p>
                            
                            <ul class="features">
                                <?php foreach ($tier['features'] as $feature): ?>
                                    <li><?= htmlspecialchars($feature) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <?php if ($tierId === $currentSubscription): ?>
                                <button class="btn-upgrade btn-current" disabled>✓ Aktuální plán</button>
                            <?php elseif ($tierId < $currentSubscription): ?>
                                <button class="btn-upgrade btn-current" disabled>Máte vyšší plán</button>
                            <?php else: ?>
                                <form method="POST" action="/dmp/api/checkout/process.php" style="margin-top: 1rem;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="tier" value="<?= $tierId ?>">
                                    <input type="hidden" name="tier_name" value="<?= htmlspecialchars($tier['name']) ?>">
                                    <input type="hidden" name="price" value="<?= htmlspecialchars($tier['price']) ?>">
                                    <button type="submit" class="btn-upgrade">Upgradovat na <?= htmlspecialchars($tier['name']) ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="section">
            <h3>❓ Často kladené otázky</h3>
            <div style="margin: 1.5rem 0;">
                <strong>Co se stane s mými vlastními sestavami při downgradu?</strong>
                <p>Všechny vaše existující sestavy zůstanou uloženy. Downgrade vás jen omezí v ukládání nových sestav.</p>
            </div>
            <div style="margin: 1.5rem 0;">
                <strong>Jak dlouho trvá aktivace plánu?</strong>
                <p>Aktivace je okamžitá. Hned po zaplacení budete mít přístup ke svému novému plánu.</p>
            </div>
            <div style="margin: 1.5rem 0;">
                <strong>Mohu kdykoli zrušit předplatné?</strong>
                <p>Ano, svůj plán můžete zrušit nebo změnit kdykoli bez trestů v sekci "Můj účet".</p>
            </div>
        </div>

        <?php if ($currentSubscription > 1): ?>
        <div class="section" style="border: 2px dashed #dc3545; background: #fff5f5;">
            <h3 style="color: #dc3545;">🛑 Zrušit předplatné</h3>
            <p>Chcete zrušit svůj aktuální plán a vrátit se na bezplatný tarif? Všechny vaše existující sestavy zůstanou uloženy.</p>
            <button type="button" class="btn btn-danger" onclick="document.getElementById('cancelModal').style.display='block'">Zrušit předplatné</button>
        </div>

        <!-- Confirmation Modal -->
        <div id="cancelModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
            <div style="background-color:white; margin:15% auto; padding:2rem; border-radius:12px; width:90%; max-width:400px; box-shadow:0 8px 20px rgba(0,0,0,0.3);">
                <h3 style="margin-bottom:1rem; color:#dc3545;">⚠️ Potvrďte zrušení</h3>
                <p style="margin-bottom:1rem; color:#666;">Opravdu chcete zrušit své předplatné a vrátit se na bezplatný tarif? Budete moct uložit maximálně 2 sestavy.</p>
                <div style="display:flex; gap:1rem; justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('cancelModal').style.display='none'">Zrušit</button>
                    <form method="POST" action="/dmp/api/checkout/process.php" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn btn-danger">Potvrdit zrušení</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin: 2rem 0;">
            <a href="/dmp/public/dashboard.php" class="btn btn-secondary">← Zpět na dashboard</a>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
