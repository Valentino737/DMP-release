<?php
/**
 * API endpoint pro zpracování hlášení na fóru
 * 
 * Admin/moderátor může hlášení vyřešit, zamítnout nebo skrýt obsah.
 * Akce 'hide-content' navíc označí příspěvek/komentář jako neviditelný.
 * 
 * @method POST
 * @param int    reportId   ID hlášení
 * @param string action     'resolve', 'dismiss' nebo 'hide-content'
 * @param string adminNotes Poznámky administrátora (volitelné)
 * @return JSON {success, message, data}
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

header('Content-Type: application/json');

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Kontrola, zda je uživatel admin nebo moderátor (roleId 2 nebo 3)
if (!in_array($_SESSION['roleId'], [2, 3])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validace CSRF
if (!csrf_validate()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$reportId = (int)($_POST['reportId'] ?? 0);
$action = $_POST['action'] ?? ''; // 'resolve', 'dismiss' nebo 'hide-content'
$adminNotes = trim($_POST['adminNotes'] ?? '');

if ($reportId === 0 || !in_array($action, ['resolve', 'dismiss', 'hide-content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Načtení hlášení
    $reportStmt = $pdo->prepare('SELECT * FROM forum_reports WHERE id = ?');
    $reportStmt->execute([$reportId]);
    $report = $reportStmt->fetch();

    if (!$report) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }

    $newStatus = ($action === 'dismiss') ? 'dismissed' : 'resolved';

    // Aktualizace hlášení
    $updateStmt = $pdo->prepare('
        UPDATE forum_reports 
        SET status = ?, adminNotes = ?, resolvedAt = NOW() 
        WHERE id = ?
    ');
    $updateStmt->execute([$newStatus, $adminNotes ?: null, $reportId]);

    // Pokud je akce hide-content, označit hlášený příspěvek/komentář jako neviditelný
    if ($action === 'hide-content') {
        if ($report['postId']) {
            $hideStmt = $pdo->prepare('UPDATE forum_posts SET isVisible = FALSE WHERE id = ?');
            $hideStmt->execute([$report['postId']]);
        } elseif ($report['commentId']) {
            $hideStmt = $pdo->prepare('UPDATE forum_comments SET isVisible = FALSE WHERE id = ?');
            $hideStmt->execute([$report['commentId']]);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Report ' . $newStatus,
        'data' => [
            'reportId' => $reportId,
            'status' => $newStatus,
            'action' => $action
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
