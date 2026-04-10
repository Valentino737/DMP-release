<?php
/**
 * API endpoint pro odeslání návrhu nové komponenty
 * 
 * Přihlášený uživatel může navrhnout novou komponentu ke schválení.
 * Validuje povinná pole, typ komponenty a specifikace.
 * Pro/Premium uživatelé mají prioritu při posuzování.
 * 
 * @method POST
 * @param string componentType  Typ komponenty (cpu, gpu, ram, ...)
 * @param string name           Název komponenty
 * @param string brand          Značka (volitelné)
 * @param float  price          Cena (volitelné)
 * @param array  spec_*         Specifikace jako spec_core_count, spec_tdp atd.
 * @return JSON {success, message}
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Návrhy mohou odesílat pouze přihlášení uživatelé
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Pro odeslání návrhu musíte být přihlášeni.']);
    exit;
}

// Kontrola zablokovaných uživatelů
$stmt = $pdo->prepare("SELECT is_banned FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['is_banned']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nemůžete odesílat návrhy, pokud máte ban.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Nepovolená metoda']);
    exit;
}

// Validace CSRF tokenu
if (!csrf_validate()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatný požadavek']);
    exit;
}

$data = $_POST;
$errors = [];

// Validace povinných polí
$componentType = trim($data['componentType'] ?? '');
$name = trim($data['name'] ?? '');
$brand = trim($data['brand'] ?? '');
$price = trim($data['price'] ?? '');

$validTypes = ['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'case', 'cooler'];

if (empty($componentType)) $errors[] = 'Typ komponenty je povinný';
if (!in_array($componentType, $validTypes)) $errors[] = 'Neplatný typ komponenty';
if (empty($name)) $errors[] = 'Název komponenty je povinný';
if (strlen($name) > 255) $errors[] = 'Název komponenty je příliš dlouhý (max 255 znaků)';
if (strlen($brand) > 100) $errors[] = 'Název značky je příliš dlouhý (max 100 znaků)';

if (!empty($price)) {
    if (!is_numeric($price)) {
        $errors[] = 'Cena musí být platné číslo';
    } else if ($price < 0) {
        $errors[] = 'Cena nemůže být záporná';
    } else if ($price > 999999.99) {
        $errors[] = 'Cena je příliš vysoká';
    }
}

// Validace specifikací
$specifications = [];
foreach ($data as $key => $value) {
    if (strpos($key, 'spec_') === 0) {
        $specKey = substr($key, 5);
        if (!empty($value)) {
            $specifications[$specKey] = trim($value);
        }
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Získání úrovně předplatného uživatele pro určení priority
    $userStmt = $pdo->prepare("SELECT subscription FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userSubscription = (int)($userData['subscription'] ?? 1);
    
    // Pro (2) a Premium (3) uživatelé mají prioritu
    $isPriority = in_array($userSubscription, [2, 3]) ? 1 : 0;
    
    // Vložení návrhu
    $stmt = $pdo->prepare("
        INSERT INTO component_submissions (userId, componentType, name, brand, price, specifications, status, isPriority)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $componentType,
        $name,
        !empty($brand) ? $brand : null,
        !empty($price) ? (float)$price : null,
        !empty($specifications) ? json_encode($specifications) : null,
        $isPriority
    ]);
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Váš návrh komponenty byl odeslán a bude posouzen administrátory.'
    ]);
    
} catch (Exception $e) {
    error_log('Component submission error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Při odesílání návrhu došlo k chybě']);
}
