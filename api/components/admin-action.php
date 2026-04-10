<?php
/**
 * API endpoint pro administrátorské akce s návrhy komponent
 * 
 * Umožňuje adminovi schválit, zamítnout nebo resetovat návrh komponenty.
 * Při schválení vkládá komponentu do příslušné tabulky.
 * 
 * @method POST
 * @param int    submissionId  ID návrhu
 * @param string action        'approve', 'reject' nebo 'reset'
 * @param string reason        Důvod zamítnutí (povinný pro reject)
 * @return JSON {success, message}
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Kontrola, zda je uživatel admin
if (($_SESSION['roleId'] ?? 1) !== 2) {
    http_response_code(403);
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

$submissionId = (int)($_POST['submissionId'] ?? 0);
$action = $_POST['action'] ?? '';
$reason = trim($_POST['reason'] ?? '');

$validActions = ['approve', 'reject', 'reset'];

if (!$submissionId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
    exit;
}

if (!in_array($action, $validActions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    // Získání detailů návrhu
    $stmt = $pdo->prepare("SELECT * FROM component_submissions WHERE id = ?");
    $stmt->execute([$submissionId]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Submission not found']);
        exit;
    }

    if ($action === 'approve') {
        // Získání tabulky typu komponenty a příprava dat
        $table = $submission['componentType'];
        $validTables = ['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'case', 'cooler'];

        if (!in_array($table, $validTables)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid component type']);
            exit;
        }

        // Získání struktury tabulky pro vložení pouze platných sloupců
        $describeStmt = $pdo->query("DESCRIBE `{$table}`");
        $tableColumns = [];
        foreach ($describeStmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $tableColumns[] = $col['Field'];
        }

        // Sestavení INSERT příkazu pouze se sloupci existujícími v tabulce
        $insertData = [];
        $validFields = ['name', 'brand', 'price', 'color'];

        if (in_array('name', $tableColumns) && !empty($submission['name'])) {
            $insertData['name'] = $submission['name'];
        }

        if (in_array('brand', $tableColumns) && !empty($submission['brand'])) {
            $insertData['brand'] = $submission['brand'];
        }

        if (in_array('price', $tableColumns) && !empty($submission['price'])) {
            $insertData['price'] = (float)$submission['price'];
        }

        if (in_array('color', $tableColumns) && !empty($submission['color'])) {
            $insertData['color'] = $submission['color'];
        }

        // Přidání specifikací jako jednotlivých sloupců, pokud existují v tabulce
        if (!empty($submission['specifications'])) {
            $specs = json_decode($submission['specifications'], true);
            if (is_array($specs)) {
                foreach ($specs as $key => $value) {
                    if (in_array($key, $tableColumns) && !empty($value)) {
                        $insertData[$key] = $value;
                    }
                }
            }
        }

        // Pokračovat pouze pokud máme data k vložení
        if (empty($insertData)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No valid fields to insert. Component table structure may not support this type of submission.']);
            exit;
        }

        // Sestavení INSERT dotazu
        $columns = array_keys($insertData);
        $placeholders = array_fill(0, count($columns), '?');
        $columnStr = '`' . implode('`, `', $columns) . '`';
        $placeholderStr = implode(',', $placeholders);

        $insertStmt = $pdo->prepare("
            INSERT INTO `{$table}` ({$columnStr})
            VALUES ({$placeholderStr})
        ");

        $insertStmt->execute(array_values($insertData));

        // Aktualizace stavu návrhu na schváleno
        $updateStmt = $pdo->prepare("
            UPDATE component_submissions 
            SET status = 'approved', reviewedBy = ?, reviewedAt = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$_SESSION['user_id'], $submissionId]);

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Component approved and added to database']);

    } else if ($action === 'reject') {
        if (empty($reason)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE component_submissions 
            SET status = 'rejected', rejectionReason = ?, reviewedBy = ?, reviewedAt = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$reason, $_SESSION['user_id'], $submissionId]);

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Component submission rejected']);

    } else if ($action === 'reset') {
        $stmt = $pdo->prepare("
            UPDATE component_submissions 
            SET status = 'pending', rejectionReason = NULL, reviewedBy = NULL, reviewedAt = NULL
            WHERE id = ?
        ");
        $stmt->execute([$submissionId]);

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Submission reset to pending']);
    }

} catch (Exception $e) {
    error_log('Component submission action error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
