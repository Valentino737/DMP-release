<?php
/**
 * API endpoint pro smazání sestavy
 * 
 * Přijímá POST požadavek s build_id.
 * Ověřuje vlastnictví sestavy a maže záznamy z used_parts i builds.
 * Vrací JSON odpověď.
 * 
 * @method POST
 * @param int build_id  ID sestavy ke smazání
 * @return JSON {success, message}
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

header('Content-Type: application/json');

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Validace CSRF tokenu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_validate()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Získání ID sestavy z POST nebo GET
$buildId = $_POST['build_id'] ?? $_GET['build_id'] ?? null;

if (!$buildId || !is_numeric($buildId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid build ID']);
    exit;
}

$userId = $_SESSION['user_id'];

// Ověření, že sestava patří uživateli
$stmt = $pdo->prepare("SELECT id, userId FROM builds WHERE id = ?");
$stmt->execute([$buildId]);
$build = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$build) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Build not found']);
    exit;
}

if ($build['userId'] != $userId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this build']);
    exit;
}

try {
    // Smazání záznamů used_parts přiřazených k této sestavě
    $stmt = $pdo->prepare("DELETE FROM used_parts WHERE buildId = ?");
    $stmt->execute([$buildId]);
    
    // Smazání sestavy
    $stmt = $pdo->prepare("DELETE FROM builds WHERE id = ?");
    $stmt->execute([$buildId]);
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Build deleted successfully']);
} catch (PDOException $e) {
    error_log('Build delete error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
