<?php
/**
 * API endpoint pro aktualizaci existující sestavy
 * 
 * Přijímá POST požadavek (JSON nebo formulář) s CSRF tokenem.
 * Načte editovanou sestavu ze session, ověří vlastnictví,
 * smaže staré díly a uloží nové komponenty.
 * 
 * @method POST
 * @return JSON {success, message, buildId}
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/build_helpers.php';

header('Content-Type: application/json');

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Přijetí CSRF tokenu z JSON těla nebo POST formuláře
$jsonInput = json_decode(file_get_contents('php://input'), true);
if (is_array($jsonInput) && isset($jsonInput['csrf_token'])) {
    $_POST['csrf_token'] = $jsonInput['csrf_token'];
}

// Validace CSRF tokenu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_validate()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Získání buildId ze session
$editingBuildId = $_SESSION['editing_build_id'] ?? null;

if (!$editingBuildId || !is_numeric($editingBuildId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid build ID']);
    exit;
}

$userId = $_SESSION['user_id'];
$build = $_SESSION['build'] ?? [];

// Ověření, že sestava patří uživateli
$stmt = $pdo->prepare("SELECT id, userId, name, description FROM builds WHERE id = ?");
$stmt->execute([$editingBuildId]);
$buildData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buildData) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Build not found']);
    exit;
}

if ($buildData['userId'] != $userId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this build']);
    exit;
}

// Ověření, že sestava je kompletní
if (empty($build['cpu']) || empty($build['gpu']) || empty($build['ram']) || 
    empty($build['motherboard']) || empty($build['psu']) || empty($build['case']) || 
    empty($build['storage']) || empty($build['cooling'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sestava není kompletní. Musíte vybrat všechny komponenty.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Aktualizace metadat sestavy (zachovat existující název/popis)
    $stmt = $pdo->prepare('UPDATE builds SET updatedAt = NOW() WHERE id = ? AND userId = ?');
    $stmt->execute([$editingBuildId, $userId]);

    // Smazání starých dílů a used_parts
    deleteBuildParts($pdo, $editingBuildId);

    // Uložení komponent pomocí sdílené funkce
    saveBuildComponents($pdo, $editingBuildId, $build);

    $pdo->commit();

    // Vyčištění session příznaků
    $_SESSION['build'] = null;
    $_SESSION['editing_build_id'] = null;
    $_SESSION['is_editing_build'] = false;

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Build updated successfully', 'buildId' => $editingBuildId]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Build update error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
