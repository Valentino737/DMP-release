<?php
/**
 * API endpoint pro nahlášení příspěvku nebo komentáře
 * 
 * Přihlášený uživatel může nahlásit nevhodný obsah.
 * Validuje délku důvodu (max 255) a popisu (max 2000).
 * Pro/Premium uživatelé mají prioritu při řešení hlášení.
 * 
 * @method POST
 * @param int    postId      ID příspěvku (volitelné)
 * @param int    commentId   ID komentáře (volitelné)
 * @param string reason      Důvod nahlášení (max 255 znaků)
 * @param string description Podrobný popis (max 2000 znaků, volitelné)
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

// Validace CSRF
if (!csrf_validate()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$postId = !empty($_POST['postId']) ? (int)$_POST['postId'] : null;
$commentId = !empty($_POST['commentId']) ? (int)$_POST['commentId'] : null;
$reason = trim($_POST['reason'] ?? '');
$description = trim($_POST['description'] ?? '');
$userId = $_SESSION['user_id'];

// Validace vstupu
if (empty($reason)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Reason is required']);
    exit;
}

if (mb_strlen($reason) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Reason is too long (max 255 characters)']);
    exit;
}

if (mb_strlen($description) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Description is too long (max 2000 characters)']);
    exit;
}

if ($postId === null && $commentId === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Either postId or commentId is required']);
    exit;
}

// Ověření, že příspěvek nebo komentář existuje
if ($postId !== null) {
    $checkStmt = $pdo->prepare('SELECT id FROM forum_posts WHERE id = ?');
    $checkStmt->execute([$postId]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }
} else {
    $checkStmt = $pdo->prepare('SELECT id FROM forum_comments WHERE id = ?');
    $checkStmt->execute([$commentId]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit;
    }
}

try {
    // Získání úrovně předplatného uživatele pro určení priority
    $userStmt = $pdo->prepare("SELECT subscription FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userSubscription = (int)($userData['subscription'] ?? 1);
    
    // Pro (2) a Premium (3) uživatelé mají prioritu
    $isPriority = in_array($userSubscription, [2, 3]) ? 1 : 0;
    
    $insertStmt = $pdo->prepare('INSERT INTO forum_reports (postId, commentId, reportedByUserId, reason, description, status, isPriority, createdAt)
                                VALUES (?, ?, ?, ?, ?, "pending", ?, NOW())');
    $insertStmt->execute([$postId, $commentId, $userId, $reason, $description ?: null, $isPriority]);

    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
