<?php
/**
 * Správa moderátorů
 * 
 * Umožňuje adminovi přidělovat a odebírat roli moderátora.
 * Přístupné pouze adminům (roleId=2).
 */
session_start();
require_once(__DIR__ . '/../../db/connection.php');
require_once(__DIR__ . '/../../includes/csrf.php');

// Pouze admin (roleId=2) má přístup
if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) {
    header('Location: /dmp/public/login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'User';
$errors = [];
$success = '';

// Promoce / Democe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = "Neplatný požadavek. Zkuste to znovu.";
    } else {
        $userId = (int)($_POST['userId'] ?? 0);
        $action = $_POST['action'] ?? '';
        
        if ($userId <= 0) {
            $errors[] = "Neplatné ID uživatele.";
        } elseif ($action === 'promote') {
            try {
                $stmt = $pdo->prepare('UPDATE users SET roleId = 3 WHERE id = ? AND roleId = 1');
                if ($stmt->execute([$userId])) {
                    if ($stmt->rowCount() > 0) {
                        $success = "Uživatel úspěšně povyšen na moderátora!";
                    } else {
                        $errors[] = "Uživatel je již moderátor nebo administrátor.";
                    }
                } else {
                    $errors[] = "Nepodařilo se povyšit uživatele.";
                }
            } catch (Exception $e) {
                error_log("Promote user error: " . $e->getMessage());
                $errors[] = "Chyba databáze.";
            }
        } elseif ($action === 'demote') {
            try {
                $stmt = $pdo->prepare('UPDATE users SET roleId = 1 WHERE id = ? AND roleId = 3');
                if ($stmt->execute([$userId])) {
                    if ($stmt->rowCount() > 0) {
                        $success = "Moderátor úspěšně degradován na uživatele!";
                    } else {
                        $errors[] = "Uživatel není moderátor.";
                    }
                } else {
                    $errors[] = "Nepodařilo se degradovat moderátora.";
                }
            } catch (Exception $e) {
                error_log("Demote moderator error: " . $e->getMessage());
                $errors[] = "Chyba databáze.";
            }
        } else {
            $errors[] = "Neplatná akce.";
        }
    }
}

    // Get all moderators
try {
    $stmt = $pdo->prepare('
        SELECT id, username, email, createdAt 
        FROM users 
        WHERE roleId = 3
        ORDER BY createdAt DESC
    ');
    $stmt->execute();
    $moderators = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Get moderators error: " . $e->getMessage());
    $moderators = [];
}

// Získaní všech běžných uživatelů pro možnost povýšení
try {
    $stmt = $pdo->prepare('
        SELECT id, username, email, createdAt 
        FROM users 
        WHERE roleId = 1
        ORDER BY username ASC
        LIMIT 50
    ');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Get users error: " . $e->getMessage());
    $users = [];
}

?>

<?php include_once('../../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa moderátorů - Admin panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>

<?php include_once('../../includes/navbar.php'); ?>

<div class="container py-5">
    <div class="mb-4">
        <h2>👥 Správa moderátorů</h2>
        <p class="text-muted">Povyšovat uživatele na moderátory nebo je degradovat zpět</p>
        <a href="/dmp/public/moderator/index.php" class="btn btn-secondary btn-sm">← Zpět na panel moderátora</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">👥 Aktuální moderátoři (<?= count($moderators) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($moderators)): ?>
                        <p class="text-muted">Žádní moderátoři.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($moderators as $mod): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($mod['username']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($mod['email']) ?></small>
                                    <br>
                                    <small class="text-muted">Od <?= date('d. m. Y', strtotime($mod['createdAt'])) ?></small>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="userId" value="<?= $mod['id'] ?>">
                                    <input type="hidden" name="action" value="demote">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Sesadit tohoto moderátora na uživatele?')">
                                        Sesadit
                                    </button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info">
                    <h5 class="mb-0">⬆️ Povyšit na moderátora</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <p class="text-muted">Nejsou k dispozici žádní uživatelé k povyšení.</p>
                    <?php else: ?>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="userId" class="form-label">Vybrat uživatele</label>
                                <select class="form-select" id="userId" name="userId" required>
                                    <option value="">-- Vyberte uživatele --</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>">
                                        <?= htmlspecialchars($user['username']) ?> 
                                        (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" name="action" value="promote">
                            <button type="submit" class="btn btn-warning w-100">
                                Povyšit na moderátora
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>ℹ️ O moderátorech:</strong>
                <ul class="mb-0 mt-2">
                    <li>Mohou prohlížet a zpracovávat hlášení fóra</li>
                    <li>Mohou mazat nevhodné příspěvky a komentáře</li>
                    <li>Nemá přístup k admin panelu ani ke správě komponent</li>
                    <li>Všechny akce moderátora jsou zaznamenávány</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
