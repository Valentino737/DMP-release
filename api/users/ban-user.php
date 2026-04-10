<?php
/**
 * API endpoint pro zablokování/odblokování uživatele
 * 
 * Admin nebo moderátor může zablokovat uživatele (ban) nebo odblokovat (unban).
 * Moderátor nemůže blokovat adminy. Nelze zablokovat sám sebe.
 * 
 * @method POST
 * @param int    userId    ID cílového uživatele
 * @param string action    'ban' nebo 'unban'
 * @param string banReason Důvod zablokování (volitelné)
 * @return JSON {success, message, data}
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

header('Content-Type: application/json');

// Kontrola přihlášení a role (admin nebo moderátor)
if (!isset($_SESSION['user_id']) || !in_array(($_SESSION['roleId'] ?? 1), [2, 3])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
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

$userId = (int)($_POST['userId'] ?? 0);
$banReason = trim($_POST['banReason'] ?? '');
$action = $_POST['action'] ?? ''; // 'ban' nebo 'unban'

if ($userId === 0 || !in_array($action, ['ban', 'unban'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Zabránění zablokování sebe sama
if ($userId === $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot ban yourself']);
    exit;
}

// Zabránění moderátorům v blokování adminů
if ($_SESSION['roleId'] === 3) { // Pokud je moderátor
    $userStmt = $pdo->prepare('SELECT roleId FROM users WHERE id = ?');
    $userStmt->execute([$userId]);
    $targetUser = $userStmt->fetch();
    
    if ($targetUser && $targetUser['roleId'] === 2) { // Cíl je admin
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Moderators cannot ban admins']);
        exit;
    }
}

try {
    // Načtení uživatele
    $userStmt = $pdo->prepare('SELECT id, username, is_banned FROM users WHERE id = ?');
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($action === 'ban') {
        if ($user['is_banned']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User is already banned']);
            exit;
        }
        
        // Zablokování uživatele
        $banStmt = $pdo->prepare('
            UPDATE users 
            SET is_banned = TRUE, bannedAt = NOW(), banReason = ?, bannedBy = ?
            WHERE id = ?
        ');
        $banStmt->execute([$banReason ?: 'No reason provided', $_SESSION['user_id'], $userId]);

        echo json_encode([
            'success' => true,
            'message' => 'User banned successfully',
            'data' => [
                'userId' => $userId,
                'username' => $user['username'],
                'action' => 'banned'
            ]
        ]);
    } else { // odblokování
        if (!$user['is_banned']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User is not banned']);
            exit;
        }
        
        // Odblokování uživatele a ověření emailu, aby se mohl přihlásit
        $unbanStmt = $pdo->prepare('
            UPDATE users 
            SET is_banned = FALSE, bannedAt = NULL, banReason = NULL, bannedBy = NULL, email_verified = TRUE
            WHERE id = ?
        ');
        $unbanStmt->execute([$userId]);

        echo json_encode([
            'success' => true,
            'message' => 'User unbanned successfully',
            'data' => [
                'userId' => $userId,
                'username' => $user['username'],
                'action' => 'unbanned'
            ]
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
