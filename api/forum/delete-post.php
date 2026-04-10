<?php
/**
 * API endpoint pro smazání příspěvku na fóru
 * 
 * Provádí soft delete (isVisible=FALSE) příspěvku a všech jeho komentářů.
 * Automaticky vyřeší čekající hlášení o tomto příspěvku.
 * Přístup: vlastník, admin nebo moderátor.
 * 
 * @method POST
 * @param int id|postId  ID příspěvku ke smazání
 * @return redirect  Přesměrování na forum.php
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    header('Location: /dmp/public/login.php');
    exit;
}

// Vyžadovat POST metodu pro destruktivní akci
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo '405 - Method Not Allowed';
    exit;
}

if (!csrf_validate()) {
    http_response_code(403);
    echo '403 - Invalid CSRF token';
    exit;
}

$postId = (int)($_POST['id'] ?? $_POST['postId'] ?? 0);

if ($postId === 0) {
    header('Location: /dmp/public/forum.php');
    exit;
}

try {
    // Načtení příspěvku
    $postStmt = $pdo->prepare('SELECT userId FROM forum_posts WHERE id = ?');
    $postStmt->execute([$postId]);
    $post = $postStmt->fetch();

    if (!$post) {
        header('HTTP/1.0 404 Not Found');
        echo '404 - Post not found';
        exit;
    }

    // Kontrola, zda je uživatel vlastník, admin nebo moderátor (roleId 2 nebo 3)
    if ($_SESSION['user_id'] !== $post['userId'] && !in_array($_SESSION['roleId'], [2, 3])) {
        header('HTTP/1.0 403 Forbidden');
        echo '403 - Unauthorized';
        exit;
    }

    // Smazání příspěvku (soft delete - označení jako neviditelný)
    $deleteStmt = $pdo->prepare('UPDATE forum_posts SET isVisible = FALSE WHERE id = ?');
    $deleteStmt->execute([$postId]);

    // Skrytí všech komentářů patřících k tomuto příspěvku
    $hideCommentsStmt = $pdo->prepare('UPDATE forum_comments SET isVisible = FALSE WHERE postId = ?');
    $hideCommentsStmt->execute([$postId]);

    // Automatické vyřešení čekajících hlášení o tomto příspěvku
    $resolveReportsStmt = $pdo->prepare('
        UPDATE forum_reports 
        SET status = "resolved", adminNotes = "Content was deleted" 
        WHERE postId = ? AND status = "pending"
    ');
    $resolveReportsStmt->execute([$postId]);

    header('Location: /dmp/public/forum.php');
    exit;
} catch (PDOException $e) {
    header('HTTP/1.0 500 Server Error');
    echo '500 - Server Error';
    exit;
}
