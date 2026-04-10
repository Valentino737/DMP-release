<?php
/**
 * API endpoint pro nahrání/smazání obrázku sestavy
 * 
 * Podporuje akce 'upload' a 'delete'.
 * Validuje typ souboru (JPG, PNG, GIF, WebP) a velikost (max 5 MB).
 * Ukládá soubory do assets/images/builds/.
 * 
 * @method POST
 * @param int    buildId  ID sestavy
 * @param string action   'upload' nebo 'delete'
 * @param file   image    Nahrávaný obrázek (pouze pro upload)
 * @return JSON {success, message, image_path}
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

$buildId = (int)($_POST['buildId'] ?? 0);
$action = $_POST['action'] ?? 'upload'; // 'upload' nebo 'delete'

if ($buildId === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Build ID is required']);
    exit;
}

// Ověření, že sestava existuje a patří uživateli
$buildStmt = $pdo->prepare('SELECT id, userId, image_path FROM builds WHERE id = ? AND userId = ?');
$buildStmt->execute([$buildId, $_SESSION['user_id']]);
$build = $buildStmt->fetch(PDO::FETCH_ASSOC);

if (!$build) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Build not found or you don\'t have permission']);
    exit;
}

// Zpracování akce smazání
if ($action === 'delete') {
    try {
        // Smazání souboru z disku, pokud existuje
        if ($build['image_path'] && file_exists(__DIR__ . '/../../' . $build['image_path'])) {
            unlink(__DIR__ . '/../../' . $build['image_path']);
        }
        
        // Aktualizace databáze
        $stmt = $pdo->prepare('UPDATE builds SET image_path = NULL, image_mime_type = NULL, image_uploaded_at = NULL WHERE id = ?');
        $stmt->execute([$buildId]);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting image']);
        exit;
    }
}

// Zpracování akce nahrání
if ($action !== 'upload') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Kontrola, zda byl soubor nahrán
if (empty($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Validace velikosti souboru
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit;
}

// Validace typu souboru
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP']);
    exit;
}

// Vygenerování bezpečného názvu souboru
$ext = match($mimeType) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    default => 'jpg'
};

$filename = 'build_' . $buildId . '_' . time() . '.' . $ext;
$uploadDir = __DIR__ . '/../../assets/images/builds/';
$uploadPath = 'assets/images/builds/' . $filename;

// Vytvoření adresáře, pokud neexistuje
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    // Smazání starého obrázku, pokud existuje
    if ($build['image_path'] && file_exists(__DIR__ . '/../../' . $build['image_path'])) {
        unlink(__DIR__ . '/../../' . $build['image_path']);
    }
    
    // Přesunutí nahraného souboru
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Aktualizace databáze
    $stmt = $pdo->prepare('UPDATE builds SET image_path = ?, image_mime_type = ?, image_uploaded_at = NOW() WHERE id = ?');
    $stmt->execute([$uploadPath, $mimeType, $buildId]);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'image_path' => $uploadPath
    ]);
} catch (Exception $e) {
    error_log('Build image upload error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error uploading image']);
}
