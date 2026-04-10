<?php
/**
 * API endpoint pro výpis hlášení na fóru
 * 
 * Vrací stránkovaný seznam hlášení s počty dle stavu.
 * Prioritní hlášení (od Pro/Premium uživatelů) jsou nahoře.
 * Přístup: admin nebo moderátor.
 * 
 * @method GET
 * @param string status  Filtr stavu ('pending', 'resolved', 'dismissed')
 * @param int    page    Číslo stránky (výchozí 1)
 * @return JSON {success, data, pagination, statusCounts}
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';

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

$status = $_GET['status'] ?? 'pending';
$page = (int)($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    // Získání počtů pro každý stav
    $countStmt = $pdo->prepare('SELECT status, COUNT(*) as count FROM forum_reports GROUP BY status');
    $countStmt->execute();
    $statusCounts = [];
    foreach ($countStmt->fetchAll() as $row) {
        $statusCounts[$row['status']] = $row['count'];
    }

    // Načtení hlášení se stránkováním
    $reportsStmt = $pdo->prepare('
        SELECT 
            fr.id, fr.reason, fr.description, fr.status, fr.createdAt, fr.resolvedAt, fr.isPriority,
            fr.postId, fr.commentId,
            CASE 
                WHEN fr.postId IS NOT NULL THEN fp.title
                WHEN fr.commentId IS NOT NULL THEN CONCAT("Comment: ", LEFT(fc.content, 50), "...")
                ELSE "Deleted"
            END as reported_content,
            u.username as reported_by,
            fr.adminNotes
        FROM forum_reports fr
        LEFT JOIN forum_posts fp ON fr.postId = fp.id
        LEFT JOIN forum_comments fc ON fr.commentId = fc.id
        LEFT JOIN users u ON fr.reportedByUserId = u.id
        WHERE fr.status = ?
        ORDER BY fr.isPriority DESC, fr.createdAt DESC
        LIMIT ? OFFSET ?
    ');
    $reportsStmt->execute([$status, $perPage, $offset]);
    $reports = $reportsStmt->fetchAll();

    $totalReports = $statusCounts[$status] ?? 0;
    $totalPages = ceil($totalReports / $perPage);

    echo json_encode([
        'success' => true,
        'data' => $reports,
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $totalReports,
            'totalPages' => $totalPages
        ],
        'statusCounts' => $statusCounts
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
