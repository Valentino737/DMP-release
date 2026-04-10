<?php
/**
 * Vytvoření nového příspěvku na fóru
 * 
 * Formulář pro vytvoření příspěvku s možností připojit sestavu.
 * Vyžaduje přihlášení a aktivní účet (bez banu).
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    header('Location: /dmp/public/login.php');
    exit;
}

// Kontrola, zda není uživatel zabanován
$banStmt = $pdo->prepare('SELECT is_banned FROM users WHERE id = ?');
$banStmt->execute([$_SESSION['user_id']]);
$banResult = $banStmt->fetch();
if ($banResult && $banResult['is_banned']) {
    header('Location: /dmp/public/forum.php?error=banned');
    exit;
}

$errors = [];
$userId = $_SESSION['user_id'];

// Zpracování odeslání formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validace CSRF tokenu
    if (!csrf_validate()) {
        $errors[] = "Neplatný požadavek. Zkuste to prosím znovu.";
    }
    
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $buildId = !empty($_POST['buildId']) ? (int)$_POST['buildId'] : null;
    
    // Validace
    if (empty($title)) {
        $errors[] = "Název příspěvku je povinný.";
    } elseif (strlen($title) < 3) {
        $errors[] = "Název musí mít alespoň 3 znaky.";
    } elseif (strlen($title) > 255) {
        $errors[] = "Název nesmí přesáhnout 255 znaků.";
    }
    
    if (empty($content)) {
        $errors[] = "Obsah příspěvku je povinný.";
    } elseif (strlen($content) < 10) {
        $errors[] = "Obsah musí mít alespoň 10 znaků.";
    }
    
    // Validace buildId, pokud je poskytnut (musí patřit uživateli)
    if ($buildId !== null) {
        $buildStmt = $pdo->prepare('SELECT id FROM builds WHERE id = ? AND userId = ?');
        $buildStmt->execute([$buildId, $userId]);
        if (!$buildStmt->fetch()) {
            $errors[] = "Neplatná sestava.";
            $buildId = null;
        }
    }
    
    // Pokud nejsou žádné chyby, vytvoří příspěvek
    if (empty($errors)) {
        try {
            $insertStmt = $pdo->prepare('INSERT INTO forum_posts (userId, buildId, title, content, isVisible, createdAt, updatedAt)
                                        VALUES (?, ?, ?, ?, TRUE, NOW(), NOW())');
            $insertStmt->execute([$userId, $buildId, $title, $content]);
            
            $postId = $pdo->lastInsertId();
            header('Location: /dmp/public/forum_post.php?id=' . $postId);
            exit;
        } catch (PDOException $e) {
            $errors[] = "Při vytváření příspěvku došlo k chybě. Zkuste to prosím znovu.";
        }
    }
}

// Získání sestav uživatele pro možnost přiřazení k příspěvku
$buildsStmt = $pdo->prepare('SELECT id, name FROM builds WHERE userId = ? ORDER BY createdAt DESC');
$buildsStmt->execute([$userId]);
$userBuilds = $buildsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vytvořit příspěvek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><i class="bi bi-plus-circle"></i> Vytvořit příspěvek</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Opravte prosím následující chyby:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>

                            <div class="mb-4">
                                <label for="title" class="form-label fw-bold">Název příspěvku</label>
                                <input type="text" class="form-control form-control-lg <?php echo empty($_POST) ? '' : (!empty($_POST['title']) ? 'is-valid' : 'is-invalid'); ?>"
                                       id="title" name="title" 
                                       placeholder="Zadejte výstižný název příspěvku"
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                                <small class="text-muted">Buďte jasní a konkrétní</small>
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label fw-bold">Obsah příspěvku</label>
                                <textarea class="form-control <?php echo empty($_POST) ? '' : (!empty($_POST['content']) ? 'is-valid' : 'is-invalid'); ?>"
                                          id="content" name="content" rows="8"
                                          placeholder="Podělte se o své myšlenky, otázky nebo zkušenosti..."
                                          required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                <small class="text-muted">Minimálně 10 znaků.</small>
                            </div>

                            <div class="mb-4">
                                <label for="buildId" class="form-label fw-bold">Přiřazená sestava (volitelné)</label>
                                <select class="form-select form-select-lg" id="buildId" name="buildId">
                                    <option value="">-- Vyberte sestavu (volitelné) --</option>
                                    <?php foreach ($userBuilds as $build): ?>
                                        <option value="<?php echo $build['id']; ?>" 
                                                <?php echo isset($_POST['buildId']) && $_POST['buildId'] == $build['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($build['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Sdílejte jednu ze svých sestav s tímto příspěvkem</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                    <i class="bi bi-send"></i> Přidat na fórum
                                </button>
                                <a href="/dmp/public/forum.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-arrow-left"></i> Zrušit
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <strong><i class="bi bi-info-circle"></i> Pravidla fóra:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Buďte slušní a konstruktivní</li>
                        <li>Držte se tématu a pište relevantně o PC sestavách</li>
                        <li>Žádný spam, obtěžování nebo nevhodný obsah</li>
                        <li>Porušení může vést ke smazání příspěvku nebo pozastavení účtu</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
