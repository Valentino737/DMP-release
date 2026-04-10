<?php
/**
 * Dokončení a uložení sestavy
 * 
 * Zobrazuje souhrn sestavy před uložením do databáze.
 * Kontroluje kompletnost, kompatibilitu a limit sestav dle předplatného.
 * Vyžaduje přihlášení.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/build_helpers.php';

$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';
$buildName = '';
$buildDescription = '';
$saveError = '';

// Zkontroluje přihlášení
if (!$loggedIn) {
    $saveError = 'Musíte být přihlášeni, abyste mohli uložit sestavu.';
}

// Zkontroluje, zda upravujeme existující sestavu
$editingBuildId = $_SESSION['editing_build_id'] ?? null;

// Zpracuje POST požadavek pro uložení sestavy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_build'])) {
    if (!csrf_validate()) {
        $saveError = 'Neplatný CSRF token';
    } elseif (!$loggedIn) {
        $saveError = 'Musíte být přihlášeni.';
    } else {
        $buildName = trim($_POST['build_name'] ?? '');
        $buildDescription = trim($_POST['build_description'] ?? '');
        
        if (empty($buildName)) {
            $saveError = 'Název sestavy je povinný.';
        } else {
            try {
                $build = $_SESSION['build'] ?? [];
                
                // Zkontroluje, zda je sestava kompletní
                if (empty($build['cpu']) || empty($build['gpu']) || empty($build['ram']) || 
                    empty($build['motherboard']) || empty($build['psu']) || empty($build['case']) || 
                    empty($build['storage']) || empty($build['cooling'])) {
                    $saveError = 'Sestava není kompletní. Musíte vybrat všechny komponenty.';
                } else if (!$editingBuildId) {
                    // Zkontroluje limity předplatného pouze pro NOVÉ sestavy (úpravy nejsou omezeny)
                    $userStmt = $pdo->prepare('SELECT subscription FROM users WHERE id = ?');
                    $userStmt->execute([$_SESSION['user_id']]);
                    $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
                    $subscription = (int)($userInfo['subscription'] ?? 1);
                    
                    // Získá aktuální počet sestav
                    $countStmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM builds WHERE userId = ?');
                    $countStmt->execute([$_SESSION['user_id']]);
                    $buildCount = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
                    
                    // Definuje limity: zdarma=2, pro=6, premium=neomezeno
                    $limits = [1 => 2, 2 => 6, 3 => 999999];
                    $limit = $limits[$subscription] ?? 2;
                    
                    if ($buildCount >= $limit) {
                        $tierName = ['Zdarma', 'Pro', 'Premium'][$subscription - 1] ?? 'Zdarma';
                        $saveError = 'Dosáhli jste limitu sestav pro vaši úroveň (' . $tierName . ': max. ' . $limit . ' sestav).';
                        $showUpgradeLink = true;
                    }
                }
                
                if (!$saveError) {
                    $pdo->beginTransaction();
                    try {
                        if ($editingBuildId) {
                            // Aktualizuje existující sestavu
                            $stmt = $pdo->prepare('
                                UPDATE builds
                                SET name = ?, description = ?, updatedAt = NOW()
                                WHERE id = ? AND userId = ?
                            ');
                            $stmt->execute([
                                $buildName,
                                $buildDescription ?: null,
                                $editingBuildId,
                                $_SESSION['user_id']
                            ]);

                            $buildId = $editingBuildId;

                            // Smaže staré komponenty a použité komponenty
                            deleteBuildParts($pdo, $buildId);
                        } else {
                            // Vloží nový záznam sestavy
                            $stmt = $pdo->prepare('
                                INSERT INTO builds (userId, name, description, isPublic, createdAt, updatedAt)
                                VALUES (?, ?, ?, 0, NOW(), NOW())
                            ');
                            $stmt->execute([
                                $_SESSION['user_id'],
                                $buildName,
                                $buildDescription ?: null
                            ]);

                            $buildId = (int)$pdo->lastInsertId();
                        }

                        // Uloží komponenty pomocí sdílené funkce
                        saveBuildComponents($pdo, $buildId, $build);

                        $pdo->commit();

                        // Vymaže session sestavy a flagy úprav
                        $_SESSION['build'] = null;
                        $_SESSION['editing_build_id'] = null;
                        $_SESSION['is_editing_build'] = false;

                        // Přesměruje na dashboard
                        header("Location: /dmp/public/dashboard.php");
                        exit;
                    } catch (Exception $txEx) {
                        $pdo->rollBack();
                        throw $txEx;
                    }
                }
            } catch (Exception $e) {
                $saveError = 'Chyba při ukládání. Zkuste to prosím znovu.';
            }
        }
    }
}

$build = $_SESSION['build'] ?? [];
$buildComplete = !empty($build['cpu']) && !empty($build['gpu']) && !empty($build['ram']) && 
                 !empty($build['motherboard']) && !empty($build['psu']) && !empty($build['case']) && 
                 !empty($build['storage']) && !empty($build['cooling']);

// Použije sdílenou validaci z build_helpers.php
$buildValidation = checkCompatibility($build);
$totalPrice = calculateBuildPrice($build);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Konfigurátor - Dokončit sestavu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        html, body {
            height: 100%;
        }
        body {
            background-color: #f9fafb;
            color: #0A0908;
            display: flex;
            flex-direction: column;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .success-banner {
            background: #28a745;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            text-align: center;
        }
        .error-banner {
            background: #dc3545;
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        .btn-save {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn-save:hover {
            background: #45a049;
        }
        .btn-save:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .nav-top {
            background: white;
            border-bottom: 1px solid #ddd;
            padding: 12px 0;
            margin-bottom: 24px;
            flex-shrink: 0;
        }
        .nav-top a {
            color: #0A0908;
            text-decoration: none;
            font-weight: 600;
            size: 14px;
        }
        footer {
            background: #0A0908;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="nav-top">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <a href="/dmp/public/index.php">← Zpět na domovskou stránku</a>
        </div>
    </div>

    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px 40px 20px; flex: 1;">
        <?php $isEditing = !empty($editingBuildId); ?>
            <h1><?= $isEditing ? 'Uložit změny sestavy' : 'Dokončit sestavu' ?></h1>

            <?php if ($saveError): ?>
                <div class="error-banner">
                    <?= htmlspecialchars($saveError) ?>
                    <?php if (!empty($showUpgradeLink)): ?>
                        <a href="/dmp/public/upgrade.php" style="color: #fff; text-decoration: underline; margin-left: 6px; font-weight: 600;">Upgradujte nyní</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Sekce rekapitulace sestavy -->
            <?php if (!empty($build)): ?>
            <div style="background: white; border: 1px solid #DEE5E5; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
                <h2 style="font-size: 1.5rem; margin-bottom: 20px;">📋 Rekapitulace sestavy</h2>
                
                <!-- Problémy s validací -->
                <?php if (!empty($buildValidation['errors'])): ?>
                    <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; padding: 12px; margin-bottom: 16px;">
                        <strong style="color: #721c24;">⚠️ Problémy s kompatibilitou:</strong>
                        <ul style="margin: 8px 0 0 12px; color: #721c24;">
                            <?php foreach ($buildValidation['errors'] as $issue): ?>
                                <li><?= htmlspecialchars($issue) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Upozornění -->
                <?php if (!empty($buildValidation['warnings'])): ?>
                    <div style="background: #fff3cd; border: 1px solid #ffeeba; border-radius: 6px; padding: 12px; margin-bottom: 16px;">
                        <strong style="color: #856404;">💡 Upozornění:</strong>
                        <ul style="margin: 8px 0 0 12px; color: #856404;">
                            <?php foreach ($buildValidation['warnings'] as $warning): ?>
                                <li><?= htmlspecialchars($warning) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Seznam komponent -->
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #DEE5E5;">
                            <th style="text-align: left; padding: 12px 0; font-weight: 700;">Komponenta</th>
                            <th style="text-align: left; padding: 12px 0; font-weight: 700;">Vybrané</th>
                            <th style="text-align: right; padding: 12px 0; font-weight: 700;">Cena</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- CPU -->
                        <tr style="border-bottom: 1px solid #DEE5E5;">
                            <td style="padding: 12px 0;">🖥️ Procesor (CPU)</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['cpu'])): ?>
                                    <?= htmlspecialchars($build['cpu']['name'] ?? 'N/A') ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php echo number_format((float)($build['cpu']['price'] ?? 0), 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                        
                        <!-- GPU -->
                        <tr style="border-bottom: 1px solid #DEE5E5;">
                            <td style="padding: 12px 0;">🎮 Grafická karta (GPU)</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['gpu'])): ?>
                                    <?= htmlspecialchars($build['gpu']['name'] ?? 'N/A') ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php echo number_format((float)($build['gpu']['price'] ?? 0), 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                        
                        <!-- RAM -->
                        <tr style="border-bottom: 1px solid #DEE5E5;">
                            <td style="padding: 12px 0;">🧠 Operační paměť (RAM)</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['ram'])): ?>
                                    <?= htmlspecialchars($build['ram']['name'] ?? 'N/A') ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php echo number_format((float)($build['ram']['price'] ?? 0), 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                        
                        <!-- Základní deska -->
                        <tr style="border-bottom: 1px solid #DEE5E5;">
                            <td style="padding: 12px 0;">🖲️ Základní deska</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['motherboard'])): ?>
                                    <?= htmlspecialchars($build['motherboard']['name'] ?? 'N/A') ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php echo number_format((float)($build['motherboard']['price'] ?? 0), 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                        
                        <!-- PSU -->
                        <tr style="border-bottom: 1px solid #DEE5E5;">
                            <td style="padding: 12px 0;">⚡ Zdroj napájení (PSU)</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['psu'])): ?>
                                    <?= htmlspecialchars($build['psu']['name'] ?? 'N/A') ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php echo number_format((float)($build['psu']['price'] ?? 0), 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                        
                        <!-- PC Skříň -->
                        <tr style="border-bottom: 1px solid #DEE5E5;">
                            <td style="padding: 12px 0;">📦 PC Skříň</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['case'])): ?>
                                    <?= htmlspecialchars($build['case']['name'] ?? 'N/A') ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php echo number_format((float)($build['case']['price'] ?? 0), 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                        
                        <!-- Úložiště -->
                        <tr style="border-bottom: 1px solid #DEE5E5;">
                            <td style="padding: 12px 0;">💾 Úložiště</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['storage'])): ?>
                                    <?php if (is_array($build['storage'])): ?>
                                        <?php $storageCount = 0; ?>
                                        <?php foreach ($build['storage'] as $drive): ?>
                                            <?php if (is_array($drive)): ?>
                                                <?php $storageCount++; ?>
                                                <?= htmlspecialchars($drive['name'] ?? 'Úložiště') ?><br>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if ($storageCount === 0): ?>
                                            <span style="color: #999;">Nevybráno</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($build['storage']['name'] ?? 'N/A') ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php 
                                    $storagePrice = 0;
                                    if (!empty($build['storage']) && is_array($build['storage'])) {
                                        foreach ($build['storage'] as $drive) {
                                            if (is_array($drive)) {
                                                $storagePrice += (float)($drive['price'] ?? 0);
                                            }
                                        }
                                    }
                                    echo number_format($storagePrice, 0, ',', ' ') . ' Kč';
                                ?>
                            </td>
                        </tr>
                        
                        <!-- Chlazení -->
                        <tr style="border-bottom: 2px solid #618B4A;">
                            <td style="padding: 12px 0;">❄️ Chlazení</td>
                            <td style="padding: 12px 0;">
                                <?php if (!empty($build['cooling'])): ?>
                                    <?= htmlspecialchars($build['cooling']['name'] ?? 'N/A') ?>
                                <?php else: ?>
                                    <span style="color: #999;">Nevybráno</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 0; text-align: right;">
                                <?php echo number_format((float)($build['cooling']['price'] ?? 0), 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                        
                        <!-- Celková cena -->
                        <tr>
                            <td colspan="2" style="padding: 16px 0; text-align: right; font-weight: 700; font-size: 1.2rem;">Celková cena:</td>
                            <td style="padding: 16px 0; text-align: right; font-weight: 700; font-size: 1.2rem; color: #618B4A;">
                                <?php echo number_format($totalPrice, 0, ',', ' ') . ' Kč'; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!$loggedIn): ?>
                <div style="background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 6px; padding: 20px; margin-bottom: 24px;">
                    <h3 style="margin-top: 0; color: #004085;">💾 Uložit tuto sestavu</h3>
                    <p style="color: #004085; margin-bottom: 12px;">Chcete si uložit tuto sestavu pro pozdější úpravu a sdílení? <strong>Musíte být přihlášeni.</strong></p>
                    <div>
                        <a href="/dmp/public/login.php" class="btn-back" style="background: #007bff; display: inline-block; margin-right: 10px;">🔑 Přihlásit se</a>
                        <a href="/dmp/public/register.php" class="btn-back" style="background: #28a745; display: inline-block;">📝 Registrovat se</a>
                    </div>
                    <p style="margin: 12px 0 0 0; font-size: 0.9rem; color: #666;">Registrovaní uživatelé si mohou ukládat a spravovat více sestav, sdílet je v diskusi a více.</p>
                </div>
            <?php elseif ($buildComplete): ?>
                <form method="POST" style="max-width: 600px;">
                    <?= csrf_field() ?>
                    
                    <div class="form-group">
                        <label for="build_name">Název sestavy *</label>
                        <input type="text" id="build_name" name="build_name" value="<?= htmlspecialchars($buildName) ?>" placeholder="např. Gaming PC 2026" required>
                    </div>

                    <div class="form-group">
                        <label for="build_description">Popis (volitelné)</label>
                        <textarea id="build_description" name="build_description" placeholder="Popište účel nebo charakteristiky vaší sestavy..."></textarea>
                    </div>

                    <button type="submit" name="save_build" class="btn-save">Uložit sestavu</button>
                    <a href="/dmp/public/configurator.php" class="btn-back">Zpět do konfiguratoru</a>
                </form>
            <?php else: ?>
                <div style="background: #f0ad4e; border: 1px solid #eea236; border-radius: 6px; padding: 16px; margin-bottom: 24px;">
                    <p><strong>Sestava není kompletní.</strong> Musíte vybrat všechny komponenty dříve, než budete moci uložit.</p>
                    <a href="/dmp/public/configurator.php" class="btn-back" style="display: inline-block; margin-top: 12px;">Pokračovat v konfiguraci</a>
                </div>
            <?php endif; ?>
    </div>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>