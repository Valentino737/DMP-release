<?php
/**
 * Správa příspěvků na fóru
 * 
 * Zobrazuje všechny příspěvky s možností smazání.
 * Přístupné adminům a moderátorům.
 */
session_start();
require_once(__DIR__ . '/../../db/connection.php');
require_once(__DIR__ . '/../../includes/csrf.php');

// Ověření role – přístup pro admina (2) a moderátora (3)
if (!isset($_SESSION['user_id']) || !in_array(($_SESSION['roleId'] ?? 1), [2, 3])) {
    header('Location: /dmp/public/login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'User';

// Načtení všech viditelných příspěvků
try {
    $stmt = $pdo->prepare('
        SELECT fp.id, fp.title, fp.content, fp.buildId, fp.isVisible, 
               fp.createdAt, fp.updatedAt, u.username as authorUsername, u.id as userId,
               (SELECT COUNT(*) FROM forum_comments WHERE postId = fp.id AND isVisible = TRUE) as commentCount
        FROM forum_posts fp
        JOIN users u ON fp.userId = u.id
        WHERE fp.isVisible = TRUE
        ORDER BY fp.createdAt DESC
        LIMIT 100
    ');
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Manage posts error: " . $e->getMessage());
    $posts = [];
}

?>

<?php include_once('../../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa příspěvků - Panel moderátora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>

<?php include_once('../../includes/navbar.php'); ?>

<div class="container py-5">
    <div class="mb-4">
        <h2>✏️ Správa příspěvků fóra</h2>
        <p class="text-muted">Prohlédnou a moderovat příspěvky fóra</p>
        <a href="/dmp/public/moderator/index.php" class="btn btn-secondary btn-sm">← Zpět na panel moderátora</a>
    </div>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info">
            Žádné příspěvky nenalezeny.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nadpis</th>
                        <th>Autor</th>
                        <th>Komentáře</th>
                        <th>Vytvořeno</th>
                        <th>Stav</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars(mb_substr($post['title'], 0, 50, 'UTF-8')) ?></strong>
                            <?php if (mb_strlen($post['title'], 'UTF-8') > 50): ?>
                                ...
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/dmp/public/profile.php?id=<?= $post['userId'] ?>">
                                <?= htmlspecialchars($post['authorUsername']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= $post['commentCount'] ?></span>
                        </td>
                        <td>
                            <small><?= date('d. m. Y H:i', strtotime($post['createdAt'])) ?></small>
                        </td>
                        <td>
                            <?php if ($post['isVisible']): ?>
                                <span class="badge bg-success">Viditelný</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Skrytý</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/dmp/public/forum_post.php?id=<?= $post['id'] ?>" 
                               class="btn btn-sm btn-outline-primary">Zobrazit</a>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deletePost(<?= $post['id'] ?>)">Smazat</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include_once('../../includes/footer.php'); ?>


<script>
const csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8") ?>';
function deletePost(postId) {
    if (confirm('Opravdu chcete smazat tento příspěvek? Tuto akci nelze vrátit.')) {
        // Využijeme API endpoint pro mazání příspěvků, který zkontroluje CSRF token a oprávnění
        fetch('/dmp/api/forum/delete-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + postId + '&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => response.text())
        .then(data => {
            alert('Příspěvek úspěšně smazán');
            location.reload();
        })
        .catch(err => alert('Chyba při mazání příspěvku: ' + err));
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
