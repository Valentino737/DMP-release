<?php
/**
 * Správa komentářů na fóru
 * 
 * Zobrazuje všechny komentáře s možností smazání.
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

// Načtení všech viditelných komentářů
try {
    $stmt = $pdo->prepare('
        SELECT fc.id, fc.content, fc.postId, fc.isVisible, 
               fc.createdAt, fc.updatedAt, u.username as authorUsername, u.id as userId,
               fp.title as postTitle
        FROM forum_comments fc
        JOIN users u ON fc.userId = u.id
        JOIN forum_posts fp ON fc.postId = fp.id
        WHERE fc.isVisible = TRUE
        ORDER BY fc.createdAt DESC
        LIMIT 100
    ');
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Manage comments error: " . $e->getMessage());
    $comments = [];
}

?>

<?php include_once('../../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa komentářů - Panel moderátora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>

<?php include_once('../../includes/navbar.php'); ?>

<div class="container py-5">
    <div class="mb-4">
        <h2>💬 Správa komentářů</h2>
        <p class="text-muted">Prohlínout a moderovat komentáře fóra</p>
        <a href="/dmp/public/moderator/index.php" class="btn btn-secondary btn-sm">← Zpět na panel moderátora</a>
    </div>

    <?php if (empty($comments)): ?>
        <div class="alert alert-info">
            Žádné komentáře nenalezeny.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Komentář</th>
                        <th>Autor</th>
                        <th>Příspěvek</th>
                        <th>Vytvořeno</th>
                        <th>Stav</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td>
                            <small><?= htmlspecialchars(mb_substr($comment['content'], 0, 50, 'UTF-8')) ?></small>
                            <?php if (mb_strlen($comment['content'], 'UTF-8') > 50): ?>
                                ...
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/dmp/public/profile.php?id=<?= $comment['userId'] ?>">
                                <?= htmlspecialchars($comment['authorUsername']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="/dmp/public/forum_post.php?id=<?= $comment['postId'] ?>">
                                <?= htmlspecialchars(mb_substr($comment['postTitle'], 0, 30, 'UTF-8')) ?>
                            </a>
                        </td>
                        <td>
                            <small><?= date('d. m. Y H:i', strtotime($comment['createdAt'])) ?></small>
                        </td>
                        <td>
                            <?php if ($comment['isVisible']): ?>
                                <span class="badge bg-success">Viditelný</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Skrytý</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteComment(<?= $comment['id'] ?>)">Smazat</button>
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
function deleteComment(commentId) {
    if (confirm('Opravdu chcete smazat tento komentář? Tuto akci nelze vrátit.')) {
        fetch('/dmp/api/forum/delete-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'commentId=' + commentId + '&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Komentář úspěšně smazán');
                location.reload();
            } else {
                alert('Chyba: ' + (data.message || 'Nepodařilo se smazat komentář'));
            }
        })
        .catch(err => alert('Chyba při mazání komentáře: ' + err));
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
