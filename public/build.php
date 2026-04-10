<?php
/**
 * Detail sestavy
 * 
 * Zobrazuje detail konkrétní sestavy včetně komponent, ceny a popisu.
 * Vlastník může editovat popis. Přístupné všem (veřejné sestavy).
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';

$loggedIn = isset($_SESSION['user_id']);
$buildId = (int)($_GET['id'] ?? 0);
$referer = $_GET['ref'] ?? 'dashboard';
$postId = (int)($_GET['post_id'] ?? 0);
$build = null;
$components = [];
$error = '';
$isOwner = false;
$updateSuccess = false;

// Dělá update popisu sestavy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_description') {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Musíte být přihlášeni.';
    } else {
        $buildId = (int)($_POST['build_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT userId FROM builds WHERE id = ?');
        $stmt->execute([$buildId]);
        $checkBuild = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$checkBuild || $checkBuild['userId'] != $_SESSION['user_id']) {
            $error = 'Nemáte právo upravovat tuto sestavu.';
        } elseif (!csrf_validate()) {
            $error = 'Invalid request. Please try again.';
        } else {
            $newDescription = trim($_POST['description'] ?? '');
            if (strlen($newDescription) > 500) {
                $error = 'Popis nesmí být delší než 500 znaků.';
            } else {
                $stmt = $pdo->prepare('UPDATE builds SET description = ? WHERE id = ?');
                $stmt->execute([$newDescription, $buildId]);
                $updateSuccess = true;
            }
        }
    }
}

if ($buildId <= 0) {
    $error = 'Neplatné ID sestavy.';
} else {
    try {
        // Získá metadata sestavy
        $stmt = $pdo->prepare('
            SELECT b.id, b.userId, b.name, b.description, b.isPublic, b.createdAt, b.image_path, u.username AS authorName
            FROM builds b
            LEFT JOIN users u ON b.userId = u.id
            WHERE b.id = ?
        ');
        $stmt->execute([$buildId]);
        $build = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$build) {
            $error = 'Sestava nebyla nalezena.';
        } else {
            // Ověří přístup: uživatel vlastní sestavu nebo je sestava veřejná
            $isOwner = $loggedIn && $_SESSION['user_id'] == $build['userId'];
            $isPublic = $build['isPublic'];

            if (!$isOwner && !$isPublic) {
                $error = 'Nemáte právo pro prohlížení této sestavy.';
            } else {
                // Získá všechny části spojené s touto sestavou
                $stmt = $pdo->prepare('
                    SELECT p.id, p.name, p.price, 
                           p.partId_cpu, p.partId_gpu, p.partId_ram, 
                           p.partId_mboard, p.partId_storage, p.partId_psu, p.partId_case,
                           p.partId_cooler
                    FROM used_parts up
                    JOIN parts p ON up.partId = p.id
                    WHERE up.buildId = ?
                    ORDER BY p.id
                ');
                $stmt->execute([$buildId]);
                $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Pro každou část určí typ komponenty a získá kompletní data komponenty
                foreach ($parts as $part) {
                    $componentType = null;
                    $componentId = null;
                    $componentTableName = null;

                    if (($part['partId_cpu'] ?? null)) {
                        $componentType = 'cpu';
                        $componentId = $part['partId_cpu'];
                        $componentTableName = 'cpu';
                    } elseif (($part['partId_gpu'] ?? null)) {
                        $componentType = 'gpu';
                        $componentId = $part['partId_gpu'];
                        $componentTableName = 'gpu';
                    } elseif (($part['partId_ram'] ?? null)) {
                        $componentType = 'ram';
                        $componentId = $part['partId_ram'];
                        $componentTableName = 'ram';
                    } elseif (($part['partId_mboard'] ?? null)) {
                        $componentType = 'motherboard';
                        $componentId = $part['partId_mboard'];
                        $componentTableName = 'motherboard';
                    } elseif (($part['partId_storage'] ?? null)) {
                        $componentType = 'storage';
                        $componentId = $part['partId_storage'];
                        $componentTableName = 'storage';
                    } elseif (($part['partId_psu'] ?? null)) {
                        $componentType = 'psu';
                        $componentId = $part['partId_psu'];
                        $componentTableName = 'psu';
                    } elseif (($part['partId_case'] ?? null)) {
                        $componentType = 'case';
                        $componentId = $part['partId_case'];
                        $componentTableName = 'case';
                    } elseif (($part['partId_cooler'] ?? null)) {
                        $componentType = 'cooling';
                        $componentId = $part['partId_cooler'];
                        $componentTableName = 'cooler';
                    }

                    if ($componentType && $componentId) {
                        // Získá data komponenty
                        $compStmt = $pdo->prepare("SELECT * FROM `$componentTableName` WHERE id = ?");
                        $compStmt->execute([$componentId]);
                        $componentData = $compStmt->fetch(PDO::FETCH_ASSOC);

                        if ($componentData) {
                            // Nepřepisuje pole 'type' komponenty z databáze, ale přidá vlastní pole pro typ komponenty
                            if (!isset($components[$componentType])) {
                                $components[$componentType] = [];
                            }
                            $components[$componentType][] = $componentData;
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        $error = 'Chyba při načítání sestavy. Zkuste to prosím znovu.';
    }
}

// UX jména a ikonky pro typy komponent
$typeNames = [
    'cpu' => 'Procesor (CPU)',
    'gpu' => 'Grafická karta (GPU)',
    'ram' => 'Operační paměť (RAM)',
    'motherboard' => 'Základní deska',
    'storage' => 'Úložiště',
    'psu' => 'Zdroj',
    'case' => 'Skříň',
    'cooling' => 'Chlazení'
];

$typeIcons = [
    'cpu' => '⚙️',
    'gpu' => '🎮',
    'ram' => '🧠',
    'motherboard' => '🖥️',
    'storage' => '💾',
    'psu' => '⚡',
    'case' => '📦',
    'cooling' => '❄️'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Konfigurátor - <?= $build ? htmlspecialchars($build['name']) : 'Sestava' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f9fafb 0%, #f0f0f2 100%);
            color: #0A0908;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .nav-top {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 0;
            margin-bottom: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .nav-top a {
            color: #0066cc;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .nav-top a:hover {
            color: #0052a3;
        }
        
        h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .build-header {
            background: white;
            border-radius: 12px;
            padding: 40px;
            margin: 30px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
        }
        
        .build-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .meta-item {
            padding: 18px;
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f3f7 100%);
            border-radius: 10px;
            border: 1px solid #e8ecf1;
            transition: all 0.3s ease;
        }
        
        .meta-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .meta-label {
            font-weight: 700;
            color: #666;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .meta-value {
            color: #0A0908;
            font-size: 16px;
            font-weight: 600;
        }
        
        .error-banner {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 18px 24px;
            border-radius: 10px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
            border-left: 4px solid #c82333;
        }
        
        .success-banner {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            padding: 18px 24px;
            border-radius: 10px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
            border-left: 4px solid #218838;
        }
        
        .component-section {
            margin-bottom: 40px;
        }
        
        .component-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 24px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #f0f3f7 0%, #f8f9fa 100%);
            border-left: 5px solid #0066cc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }
        
        .component-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        
        .component-type-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        
        .component-card {
            background: white;
            border-radius: 10px;
            border: 1px solid #e8ecf1;
            padding: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .component-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0066cc, #0052a3);
        }
        
        .component-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
            border-color: #0066cc;
        }
        
        .component-name {
            font-weight: 800;
            font-size: 14px;
            margin-bottom: 10px;
            color: #0A0908;
            word-break: break-word;
            line-height: 1.3;
        }
        
        .component-price {
            font-size: 13px;
            font-weight: 700;
            color: #218738;
            margin-bottom: 10px;
            padding: 8px 10px;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 6px;
            display: inline-block;
        }
        
        .component-spec {
            font-size: 12px;
            color: #555;
            margin-bottom: 6px;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .component-spec:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .component-spec-label {
            font-weight: 700;
            color: #333;
            min-width: 120px;
        }
        
        .component-spec-value {
            color: #0A0908;
            font-weight: 600;
        }
        
        .btn-back, .btn-edit {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: 700;
            font-size: 14px;
            margin-right: 12px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(108, 117, 125, 0.3);
            text-decoration: none;
            color: white;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 102, 204, 0.3);
            text-decoration: none;
            color: white;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-public {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }
        
        .status-private {
            background: linear-gradient(135deg, #e8e8e8 0%, #d8d8d8 100%);
            color: #333;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        footer {
            background: #0A0908;
            color: white;
            text-align: center;
            padding: 30px;
            margin-top: auto;
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f3f7 100%);
            border-bottom: 1px solid #e8ecf1;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-title {
            font-weight: 800;
            color: #0A0908;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-footer {
            background: #f8f9fa;
            border-top: 1px solid #e8ecf1;
            padding: 16px 24px;
            border-radius: 0 0 12px 12px;
        }
        
        .modal .btn-primary {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            border: none;
        }
        
        .modal .btn-primary:hover {
            background: linear-gradient(135deg, #0052a3 0%, #004080 100%);
        }
        
        .build-image-section {
            margin-bottom: 30px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .build-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            display: block;
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f3f7 100%);
        }
        
        .build-image-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #618B4A 0%, #4a6a38 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .build-image-footer {
            background: #f8f9fa;
            border-top: 1px solid #e8ecf1;
            padding: 12px 20px;
            text-align: right;
        }
        
        .btn-sm-edit {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            white-space: nowrap;
        }
        
        .btn-sm-edit-primary {
            background: #0066cc;
            color: white;
            border: 1px solid #0066cc;
        }
        
        .btn-sm-edit-primary:hover {
            background: #0052a3;
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

    <div style="max-width: 1600px; margin: 0 auto; padding: 30px 20px 40px 20px; flex: 1 0 auto;">
        <?php if ($updateSuccess): ?>
            <div class="success-banner">
                ✓ Popis byl úspěšně aktualizován!
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error-banner"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($build): ?>
            <!-- Sekce obrázku sestavy -->
            <?php if ($build['image_path'] && file_exists(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/dmp/' . $build['image_path']))): ?>
                <div class="build-image-section">
                    <img src="/dmp/<?= htmlspecialchars($build['image_path']) ?>" alt="<?= htmlspecialchars($build['name']) ?>" class="build-image">
                    <?php if ($isOwner): ?>
                        <div class="build-image-footer">
                            <a href="/dmp/public/edit_build_image.php?id=<?= $build['id'] ?>" class="btn-sm-edit btn-sm-edit-primary">
                                🖼️ Upravit obrázek
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="build-image-section">
                    <div class="build-image-placeholder">📸</div>
                    <?php if ($isOwner): ?>
                        <div class="build-image-footer">
                            <a href="/dmp/public/edit_build_image.php?id=<?= $build['id'] ?>" class="btn-sm-edit btn-sm-edit-primary">
                                🖼️ Přidat obrázek
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="build-header">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 24px;">
                    <div style="flex: 1;">
                        <h1><?= htmlspecialchars($build['name']) ?></h1>
                        <a href="/dmp/public/profile.php?id=<?= (int)$build['userId'] ?>" style="color: #618B4A; font-size: 1.25rem; text-decoration: none; font-weight: 500; margin-top: 0.4rem; display: inline-block;">
                            <?= htmlspecialchars($build['authorName'] ?? 'Neznámý uživatel') ?>
                        </a>
                    </div>
                    <div>
                        <span class="status-badge <?= $build['isPublic'] ? 'status-public' : 'status-private' ?>">
                            <?= $build['isPublic'] ? '🌐 Veřejné' : '🔒 Soukromé' ?>
                        </span>
                    </div>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <?php if ($build['description']): ?>
                        <p style="font-size: 16px; color: #555; margin: 0; line-height: 1.6;">
                            <?= htmlspecialchars($build['description']) ?>
                        </p>
                    <?php else: ?>
                        <p style="font-size: 16px; color: #999; margin: 0; font-style: italic;">
                            Žádný popis
                        </p>
                    <?php endif; ?>
                    <?php if ($isOwner): ?>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDescriptionModal" style="margin-top: 12px;">
                            ✏️ Upravit popis
                        </button>
                    <?php endif; ?>
                </div>

                <?php
                    $totalPrice = 0;
                    foreach ($components as $typeComponents) {
                        foreach ($typeComponents as $comp) {
                            $totalPrice += (float)($comp['price'] ?? 0);
                        }
                    }
                ?>
                <div class="build-meta">
                    <div class="meta-item">
                        <div class="meta-label">📅 Vytvořeno</div>
                        <div class="meta-value"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($build['createdAt']))) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">⚙️ Počet komponent</div>
                        <div class="meta-value"><?= count(array_merge(...array_values($components))) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">💰 Celková cena</div>
                        <div class="meta-value" style="color: #218738;"><?= number_format($totalPrice, 2, ',', ' ') ?> Kč</div>
                    </div>
                </div>

                <div class="action-buttons">
                    <?php if ($loggedIn && $_SESSION['user_id'] == $build['userId']): ?>
                        <a href="/dmp/public/build_edit.php?id=<?= $build['id'] ?>" class="btn-edit">✏️ Upravit sestavu</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($components)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Tato sestava nemá žádné komponenty</p>
                    <p style="font-size: 14px;">Začněte přidáváním komponent do vašíPC konfigurace</p>
                </div>
            <?php else: ?>
                <div class="component-grid">
                    <?php 
                    $displayOrder = ['cpu', 'gpu', 'ram', 'motherboard', 'psu', 'case', 'storage', 'cooling'];
                    foreach ($displayOrder as $type):
                        if (!isset($components[$type])):
                            continue;
                        endif;
                        foreach ($components[$type] as $component):
                    ?>
                        <?php
                            // Zmapování typu komponenty na parametr pro parts.php
                            $categoryMap = [
                                'cpu' => 'cpu',
                                'gpu' => 'gpu',
                                'ram' => 'ram',
                                'motherboard' => 'motherboard',
                                'storage' => 'storage',
                                'psu' => 'psu',
                                'case' => 'case',
                                'cooling' => 'cooler'
                            ];
                            $categoryParam = $categoryMap[$type] ?? 'all';
                        ?>
                        <div class="component-card" style="cursor: pointer;" onclick="window.location.href='/dmp/public/parts.php?category=<?= $categoryParam ?>&highlight=<?= $component['id'] ?>'">
                                <div class="component-type-badge"><?= $typeIcons[$type] ?? '⚙️' ?> <?= $typeNames[$type] ?? ucfirst($type) ?></div>
                                <div class="component-name"><?= htmlspecialchars($component['name']) ?></div>

                                    <?php if (isset($component['price']) && $component['price'] > 0): ?>
                                        <div class="component-price">
                                            <?= number_format($component['price'], 2, ',', ' ') ?> Kč
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($type === 'cpu'): ?>
                                        <?php if (isset($component['core_count'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Jádra:</span>
                                                <span class="component-spec-value"><?= $component['core_count'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['thread_count'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Vlákna:</span>
                                                <span class="component-spec-value"><?= $component['thread_count'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['core_clock'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Frekvence:</span>
                                                <span class="component-spec-value"><?= number_format($component['core_clock'], 1) ?> GHz</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['tdp'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">TDP:</span>
                                                <span class="component-spec-value"><?= $component['tdp'] ?>W</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['socket'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Socket:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['socket']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['ram'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">RAM Typ:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['ram']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($type === 'gpu'): ?>
                                        <?php if (isset($component['chipset'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Chipset:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['chipset']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['memory'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">VRAM:</span>
                                                <span class="component-spec-value"><?= $component['memory'] ?>GB</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['core_clock'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Frekvence:</span>
                                                <span class="component-spec-value"><?= number_format($component['core_clock']) ?> MHz</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['tdp'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">TDP:</span>
                                                <span class="component-spec-value"><?= $component['tdp'] ?>W</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['length'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Délka:</span>
                                                <span class="component-spec-value"><?= $component['length'] ?>mm</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($type === 'ram'): ?>
                                        <?php if (isset($component['modules'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Konfigurace:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['modules']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['speed'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Rychlost:</span>
                                                <span class="component-spec-value"><?= $component['speed'] ?> MHz</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['cas_latency'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">CAS Latency:</span>
                                                <span class="component-spec-value"><?= $component['cas_latency'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($type === 'motherboard'): ?>
                                        <?php if (isset($component['socket'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Socket:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['socket']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['form_factor'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Formát:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['form_factor']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['memory_type'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">RAM Typ:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['memory_type']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['max_memory'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Max RAM:</span>
                                                <span class="component-spec-value"><?= $component['max_memory'] ?>GB</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['memory_slots'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">RAM Sloty:</span>
                                                <span class="component-spec-value"><?= $component['memory_slots'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['sata_slots'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">SATA Porty:</span>
                                                <span class="component-spec-value"><?= $component['sata_slots'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['m2_slots'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">M.2 Sloty:</span>
                                                <span class="component-spec-value"><?= $component['m2_slots'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($type === 'psu'): ?>
                                        <?php if (isset($component['wattage'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Výkon:</span>
                                                <span class="component-spec-value"><?= $component['wattage'] ?>W</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['efficiency'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Účinnost:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['efficiency']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['modular'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Modulární:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['modular']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($type === 'case'): ?>
                                        <?php if (isset($component['type'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Typ skříně:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['type']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['psu'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">PSU Typ:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['psu']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['side_panel'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Boční panel:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['side_panel']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['external_volume'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Objem:</span>
                                                <span class="component-spec-value"><?= number_format($component['external_volume'], 1) ?> L</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($type === 'storage'): ?>
                                        <?php if (isset($component['form_factor'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Formát:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['form_factor']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['capacity'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Kapacita:</span>
                                                <span class="component-spec-value"><?= $component['capacity'] ?>GB</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['interface'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Rozhraní:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['interface']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['cache'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Cache:</span>
                                                <span class="component-spec-value"><?= $component['cache'] ?>MB</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($type === 'cooling'): ?>
                                        <?php if (isset($component['type'])): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Typ chlazení:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['type']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['socket_support']) && $component['socket_support']): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Sockety:</span>
                                                <span class="component-spec-value"><?= htmlspecialchars($component['socket_support']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['tdp']) && $component['tdp']): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Max TDP:</span>
                                                <span class="component-spec-value"><?= $component['tdp'] ?>W</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['radiator_size']) && $component['radiator_size']): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Radiátor:</span>
                                                <span class="component-spec-value"><?= $component['radiator_size'] ?>mm</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['height']) && $component['height']): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Výška:</span>
                                                <span class="component-spec-value"><?= $component['height'] ?>mm</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($component['noise_level']) && $component['noise_level']): ?>
                                            <div class="component-spec">
                                                <span class="component-spec-label">Hlučnost:</span>
                                                <span class="component-spec-value"><?= $component['noise_level'] ?> dB</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                        <?php endforeach; endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>

    <!-- Modal pro úpravu popisu sestavy     -->
    <div class="modal fade" id="editDescriptionModal" tabindex="-1" aria-labelledby="editDescriptionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDescriptionLabel">Upravit popis sestavy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <?php if ($isOwner && $build): ?>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_description">
                        <input type="hidden" name="build_id" value="<?= htmlspecialchars($build['id']) ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="descriptionInput" class="form-label">Popis (max 500 znaků)</label>
                            <textarea class="form-control" id="descriptionInput" name="description" rows="5" maxlength="500"><?= htmlspecialchars($build['description'] ?? '') ?></textarea>
                            <small class="form-text text-muted d-block mt-2">
                                <span id="charCount"><?= strlen($build['description'] ?? '') ?></span>/500 znaků
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-primary">Uložit změny</button>
                    </div>
                </form>
                <?php else: ?>
                <div class="modal-body">
                    <p>Nemáte právo upravovat tuto sestavu.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavřít</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const descriptionInput = document.getElementById('descriptionInput');
        const charCount = document.getElementById('charCount');
        
        if (descriptionInput && charCount) {
            descriptionInput.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
        }
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
