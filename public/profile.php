<?php
/**
 * Veřejný profil uživatele
 * 
 * Zobrazuje veřejné informace o uživateli a jeho veřejné sestavy.
 * Přístupné všem, vyžaduje parametr ?id= s ID uživatele.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';

$currentPage = 'profile.php';

$profileId = (int)($_GET['id'] ?? 0);
if ($profileId === 0) {
    header('Location: /dmp/public/index.php');
    exit;
}

// Vezme  informace o uživateli
$stmt = $pdo->prepare("SELECT id, username, roleId, createdAt FROM users WHERE id = ?");
$stmt->execute([$profileId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    http_response_code(404);
    $notFound = true;
}

if (!isset($notFound)) {
    $roles = [1 => 'User', 2 => 'Admin', 3 => 'Moderátor'];
    $roleName = $roles[$profile['roleId'] ?? 1] ?? 'User';

    // Vezme veřejné sestavy
    $buildStmt = $pdo->prepare("
        SELECT id, name, description, createdAt
        FROM builds
        WHERE userId = ? AND isPublic = 1
        ORDER BY createdAt DESC
    ");
    $buildStmt->execute([$profileId]);
    $publicBuilds = $buildStmt->fetchAll(PDO::FETCH_ASSOC);

    // Vezme viditelné příspěvky na fóru
    $postStmt = $pdo->prepare("
        SELECT fp.id, fp.title, fp.createdAt, COUNT(fc.id) AS comment_count
        FROM forum_posts fp
        LEFT JOIN forum_comments fc ON fp.id = fc.postId AND fc.isVisible = TRUE
        WHERE fp.userId = ? AND fp.isVisible = TRUE
        GROUP BY fp.id
        ORDER BY fp.createdAt DESC
        LIMIT 20
    ");
    $postStmt->execute([$profileId]);
    $posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

    // Vezme viditelné komentáře
    $commentStmt = $pdo->prepare("
        SELECT fc.id, fc.content, fc.createdAt, fp.id AS postId, fp.title AS postTitle
        FROM forum_comments fc
        JOIN forum_posts fp ON fc.postId = fp.id
        WHERE fc.userId = ? AND fc.isVisible = TRUE AND fp.isVisible = TRUE
        ORDER BY fc.createdAt DESC
        LIMIT 20
    ");
    $commentStmt->execute([$profileId]);
    $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title><?= isset($notFound) ? 'Uživatel nenalezen' : htmlspecialchars($profile['username']) . ' – Profil' ?> - PC Konfigurátor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        body { background: #f5f5f5; }

        .profile-hero {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 2.5rem 0 2rem;
        }

        .avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #618B4A, #4a6a38);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .role-badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .role-badge.admin { background: rgba(250,204,21,0.2); color: #92400e; }
        .role-badge.mod   { background: rgba(96,165,250,0.2); color: #1e40af; }
        .role-badge.user  { background: #f0f0f0; color: #666; }

        .section-heading {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #618B4A;
            display: inline-block;
        }

        .profile-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1rem 1.15rem;
            margin-bottom: 0.75rem;
            transition: box-shadow 0.2s ease;
        }

        .profile-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .profile-card a {
            text-decoration: none;
            color: inherit;
        }

        .profile-card h6 {
            margin: 0 0 0.25rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .profile-card h6 a {
            color: #618B4A;
        }

        .profile-card h6 a:hover {
            color: #4a6a38;
            text-decoration: underline;
        }

        .profile-card .meta {
            font-size: 0.8rem;
            color: #999;
        }

        .profile-card .snippet {
            font-size: 0.85rem;
            color: #555;
            margin-top: 0.3rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .empty-note {
            color: #aaa;
            font-size: 0.9rem;
            font-style: italic;
            padding: 1rem 0;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

<?php if (isset($notFound)): ?>
    <div class="container py-5">
        <div class="text-center py-5">
            <h2>Uživatel nenalezen</h2>
            <p class="text-muted">Tento profil neexistuje.</p>
            <a href="/dmp/public/index.php" class="btn btn-outline-secondary mt-2">Zpět na hlavní stránku</a>
        </div>
    </div>
<?php else: ?>

    <div class="profile-hero">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar"><?= strtoupper(mb_substr($profile['username'], 0, 1)) ?></div>
                <div>
                    <h1 class="h3 mb-1"><?= htmlspecialchars($profile['username']) ?></h1>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php
                            $roleClass = match ((int)$profile['roleId']) {
                                2 => 'admin',
                                3 => 'mod',
                                default => 'user',
                            };
                        ?>
                        <span class="role-badge <?= $roleClass ?>"><?= htmlspecialchars($roleName) ?></span>
                        <span class="text-muted" style="font-size:0.85rem;">Členem od <?= date('d. m. Y', strtotime($profile['createdAt'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row g-4">

            <!-- Veřejné sestavy -->
            <div class="col-lg-6">
                <h4 class="section-heading">Veřejné sestavy (<?= count($publicBuilds) ?>)</h4>
                <?php if (empty($publicBuilds)): ?>
                    <p class="empty-note">Žádné veřejné sestavy.</p>
                <?php else: ?>
                    <?php foreach ($publicBuilds as $build): ?>
                        <a href="/dmp/public/build.php?id=<?= (int)$build['id'] ?>" class="profile-card" style="display:block; text-decoration:none;">
                            <h6><?= htmlspecialchars($build['name']) ?></h6>
                            <?php if ($build['description']): ?>
                                <p class="snippet"><?= htmlspecialchars($build['description']) ?></p>
                            <?php endif; ?>
                            <div class="meta"><?= date('d. m. Y', strtotime($build['createdAt'])) ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Aktivita na fóru -->
            <div class="col-lg-6">
                <h4 class="section-heading">Příspěvky na fóru (<?= count($posts) ?>)</h4>
                <?php if (empty($posts)): ?>
                    <p class="empty-note">Žádné příspěvky.</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="profile-card">
                            <h6><a href="/dmp/public/forum_post.php?id=<?= (int)$post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h6>
                            <div class="meta"><?= date('d. m. Y', strtotime($post['createdAt'])) ?> · <?= (int)$post['comment_count'] ?> komentářů</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <h4 class="section-heading mt-4">Komentáře (<?= count($comments) ?>)</h4>
                <?php if (empty($comments)): ?>
                    <p class="empty-note">Žádné komentáře.</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="profile-card">
                            <p class="snippet mb-1"><?= htmlspecialchars($comment['content']) ?></p>
                            <div class="meta">
                                k příspěvku
                                <a href="/dmp/public/forum_post.php?id=<?= (int)$comment['postId'] ?>"><?= htmlspecialchars($comment['postTitle']) ?></a>
                                · <?= date('d. m. Y', strtotime($comment['createdAt'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
