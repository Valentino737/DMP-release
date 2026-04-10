<?php
/**
 * Editace existující sestavy
 * 
 * Načte uloženou sestavu do session a přesměruje na konfigurátor
 * v režimu editace. Vyžaduje přihlášení a vlastnictví sestavy.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';

$loggedIn = isset($_SESSION['user_id']);

// Přesměrování nepřihlášeného uživatele
if (!$loggedIn) {
    header("Location: /dmp/public/login.php");
    exit;
}

$buildId = (int)($_GET['id'] ?? 0);

if ($buildId <= 0) {
    header("Location: /dmp/public/dashboard.php");
    exit;
}

try {
    // Získání metadat sestavy
    $stmt = $pdo->prepare('SELECT id, userId, name, description FROM builds WHERE id = ?');
    $stmt->execute([$buildId]);
    $build = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$build || $build['userId'] != $_SESSION['user_id']) {
        header("Location: /dmp/public/dashboard.php");
        exit;
    }

    // Získání všech komponent spojených s touto sestavou
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

    // Inicializace session sestavy
    $_SESSION['build'] = [
        'cpu' => null,
        'gpu' => null,
        'ram' => null,
        'motherboard' => null,
        'psu' => null,
        'case' => null,
        'storage' => [],
        'cooling' => null
    ];

    // Pro každou komponentu načíst skutečná data a naplnit session
    foreach ($parts as $part) {
        $componentType = null;
        $componentId = null;
        $componentTableName = null;

        if ($part['partId_cpu']) {
            $componentType = 'cpu';
            $componentId = $part['partId_cpu'];
            $componentTableName = 'cpu';
        } elseif ($part['partId_gpu']) {
            $componentType = 'gpu';
            $componentId = $part['partId_gpu'];
            $componentTableName = 'gpu';
        } elseif ($part['partId_ram']) {
            $componentType = 'ram';
            $componentId = $part['partId_ram'];
            $componentTableName = 'ram';
        } elseif ($part['partId_mboard']) {
            $componentType = 'motherboard';
            $componentId = $part['partId_mboard'];
            $componentTableName = 'motherboard';
        } elseif ($part['partId_storage']) {
            $componentType = 'storage';
            $componentId = $part['partId_storage'];
            $componentTableName = 'storage';
        } elseif ($part['partId_psu']) {
            $componentType = 'psu';
            $componentId = $part['partId_psu'];
            $componentTableName = 'psu';
        } elseif ($part['partId_case']) {
            $componentType = 'case';
            $componentId = $part['partId_case'];
            $componentTableName = 'case';
        } elseif ($part['partId_cooler']) {
            $componentType = 'cooling';
            $componentId = $part['partId_cooler'];
            $componentTableName = 'cooler';
        }

        if ($componentType && $componentId) {
            // Načtení dat komponenty
            $compStmt = $pdo->prepare("SELECT * FROM `$componentTableName` WHERE id = ?");
            $compStmt->execute([$componentId]);
            $componentData = $compStmt->fetch(PDO::FETCH_ASSOC);

            if ($componentData) {
                // Pro úložiště přidat do pole; pro ostatní nastavit jako jedinou hodnotu
                if ($componentType === 'storage') {
                    $_SESSION['build'][$componentType][] = $componentData;
                } else {
                    $_SESSION['build'][$componentType] = $componentData;
                }
            }
        }
    }

    // Uloží buildId do session, aby bylo jasné, že upravujeme
    $_SESSION['editing_build_id'] = $buildId;
    $_SESSION['is_editing_build'] = true;

    // Přesměrovat do konfigurátoru s editovacím příznakem
    header("Location: /dmp/public/configurator.php?edit=1");
    exit;

} catch (Exception $e) {
    header("Location: /dmp/public/dashboard.php");
    exit;
}
?>
