<?php
/**
 * API endpoint pro smazání komentáře na fóru
 * 
 * Provádí soft delete (isVisible=FALSE) komentáře.
 * Automaticky vyřeší čekající hlášení o tomto komentáři.
 * Přístup: vlastník, admin nebo moderátor.
 * 
 * @method POST
 * @param int commentId|id  ID komentáře ke smazání
 * @return JSON {success, message}
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!csrf_validate()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$commentId = (int)($_POST['commentId'] ?? $_POST['id'] ?? 0);

if ($commentId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
    exit;
}

try {
    // Načtení komentáře
    $commentStmt = $pdo->prepare('SELECT userId FROM forum_comments WHERE id = ?');
    $commentStmt->execute([$commentId]);
    $comment = $commentStmt->fetch();

    if (!$comment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit;
    }

    // Kontrola, zda je uživatel vlastník, admin nebo moderátor (roleId 2 nebo 3)
    if ($_SESSION['user_id'] !== $comment['userId'] && !in_array($_SESSION['roleId'], [2, 3])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Smazání komentáře (soft delete - označení jako neviditelný)
    $deleteStmt = $pdo->prepare('UPDATE forum_comments SET isVisible = FALSE WHERE id = ?');
    $deleteStmt->execute([$commentId]);

    // Automatické vyřešení čekajících hlášení o tomto komentáři
    $resolveReportsStmt = $pdo->prepare('
        UPDATE forum_reports 
        SET status = "resolved", adminNotes = "Content was deleted" 
        WHERE commentId = ? AND status = "pending"
    ');
    $resolveReportsStmt->execute([$commentId]);

    echo json_encode(['success' => true, 'message' => 'Comment deleted']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
