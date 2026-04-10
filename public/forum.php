<?php
/**
 * Fórum - seznam příspěvků
 * 
 * Zobrazuje všechny příspěvky na fóru s vyhledáváním,
 * řazením a stránkováním. Přístupné všem.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';

$currentPage = 'forum.php';

// Zkontroluje, zda je uživatel zablokován
$userIsBanned = false;
if (isset($_SESSION['user_id'])) {
    $banStmt = $pdo->prepare('SELECT is_banned FROM users WHERE id = ?');
    $banStmt->execute([$_SESSION['user_id']]);
    $banResult = $banStmt->fetch();
    $userIsBanned = $banResult && $banResult['is_banned'];
}

// Získá parametry vyhledávání a filtrování
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Vytvoří základní dotaz pro získání příspěvků s počtem komentářů a informacemi o autorovi
$queryBase = "SELECT fp.id, fp.title, fp.content, fp.createdAt, fp.userId, u.username, u.id as author_id, 
                      COUNT(fc.id) as comment_count, fp.buildId, b.name as build_name
             FROM forum_posts fp
             JOIN users u ON fp.userId = u.id
             LEFT JOIN forum_comments fc ON fp.id = fc.postId AND fc.isVisible = TRUE
             LEFT JOIN builds b ON fp.buildId = b.id
             WHERE fp.isVisible = TRUE";

$params = [];

// Přidá filtr vyhledávání
if (!empty($searchQuery)) {
    $queryBase .= " AND (fp.title LIKE ? OR fp.content LIKE ?)";
    $searchTerm = "%{$searchQuery}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$queryBase .= " GROUP BY fp.id";

// Přidá řazení
if ($sortBy === 'oldest') {
    $queryBase .= " ORDER BY fp.createdAt ASC";
} elseif ($sortBy === 'most-comments') {
    $queryBase .= " ORDER BY comment_count DESC";
} else {
    $queryBase .= " ORDER BY fp.createdAt DESC";
}

// Získá celkový počet příspěvků pro stránkování
$countQuery = "SELECT COUNT(DISTINCT fp.id) as total FROM forum_posts fp 
               WHERE fp.isVisible = TRUE";
if (!empty($searchQuery)) {
    $countQuery .= " AND (fp.title LIKE ? OR fp.content LIKE ?)";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute(!empty($searchQuery) ? [$searchTerm, $searchTerm] : []);
$totalPosts = $countStmt->fetch()['total'];
$totalPages = ceil($totalPosts / $perPage);

// Získá příspěvky s stránkováním
$query = $queryBase . " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Získá počet komponent pro každou sestavu
$buildComponentCounts = [];
foreach ($posts as $post) {
    if (!empty($post['buildId'])) {
        try {
            $compStmt = $pdo->prepare('
                SELECT COUNT(*) as count,
                       GROUP_CONCAT(
                           COALESCE(cpu.name, gpu.name, r.name, mb.name, st.name, psu.name, c.name, cool.name)
                       SEPARATOR ", ") as types
                FROM used_parts up
                LEFT JOIN parts p ON up.partId = p.id
                LEFT JOIN cpu ON p.partId_cpu = cpu.id
                LEFT JOIN gpu ON p.partId_gpu = gpu.id
                LEFT JOIN ram r ON p.partId_ram = r.id
                LEFT JOIN motherboard mb ON p.partId_mboard = mb.id
                LEFT JOIN storage st ON p.partId_storage = st.id
                LEFT JOIN psu ON p.partId_psu = psu.id
                LEFT JOIN `case` c ON p.partId_case = c.id
                LEFT JOIN cooler cool ON p.partId_cooler = cool.id
                WHERE up.buildId = ?
            ');
            $compStmt->execute([$post['buildId']]);
            $compResult = $compStmt->fetch();
            $buildComponentCounts[$post['buildId']] = $compResult;
        } catch (PDOException $e) {
            // Pokud tabulky komponent neexistují, jednoduše přeskočí
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fórum - komunitní diskuse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-fluid" style="background: linear-gradient(135deg, #DEE5E5 0%, #E2E8E2 50%, #F4F6F4 100%); padding: 40px 0;">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-8">
                    <h1 class="display-4 mb-2">Komunitní fórum</h1>
                    <p class="lead text-muted">Sdílejte sestavy, pokládejte otázky a seznamte se s dalšími PC nadšenci</p>
                </div>
                <div class="col-md-4 d-flex align-items-center justify-content-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($userIsBanned): ?>
                            <div class="alert alert-danger mb-0">
                                <strong>Zablokován:</strong> Nemůžete přispívat do fóra.
                            </div>
                        <?php else: ?>
                            <a href="/dmp/public/forum_create.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle"></i> Vytvořit příspěvek
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/dmp/public/login.php" class="btn btn-primary btn-lg">
                            Přihlásit se pro přispívání
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Sekce vyhledávání a filtrování -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control form-control-lg" name="search" 
                               placeholder="Hledat příspěvky..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-lg" name="sort" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Nejnovější</option>
                            <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Nejstarší</option>
                            <option value="most-comments" <?php echo $sortBy === 'most-comments' ? 'selected' : ''; ?>>Nejvíce komentářů</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-outline-primary w-100">Hledat</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Příspěvky na fóru -->
        <div class="forum-posts">
            <?php if (empty($posts)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i> Žádné příspěvky. Buďte první, kdo zahájí diskusi!
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-3 shadow-sm border-0 forum-post-card" onclick="window.location.href='/dmp/public/forum_post.php?id=<?php echo $post['id']; ?>';" style="cursor: pointer;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-9">
                                    <h3 class="card-title fs-4 fw-semibold mb-1">
                                        <span class="text-decoration-none">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </span>
                                    </h3>
                                    <p class="card-text text-muted" style="max-height: 60px; overflow: hidden;">
                                        <?php echo htmlspecialchars(mb_substr($post['content'], 0, 200, 'UTF-8')); ?>...
                                    </p>
                                    <small class="text-muted">
                                        <strong>Příspěvek od:</strong> 
                                        <a href="/dmp/public/profile.php?id=<?php echo $post['author_id']; ?>" class="text-decoration-none" onclick="event.stopPropagation();">
                                            <?php echo htmlspecialchars($post['username']); ?>
                                        </a>
                                        dne <?php echo date('d. m. Y H:i', strtotime($post['createdAt'])); ?>
                                    </small>
                                    <?php if (!empty($post['build_name'])): ?>
                                        <br>
                                        <small class="badge bg-info">
                                            <i class="bi bi-pc-display"></i> Sestava: <?php echo htmlspecialchars($post['build_name']); ?>
                                        </small>
                                        <?php if (!empty($buildComponentCounts[$post['buildId']])): ?>
                                            <br>
                                            <small class="text-muted mt-2 d-inline-block">
                                                <strong><?php echo $buildComponentCounts[$post['buildId']]['count']; ?> komponent:</strong>
                                                <?php echo htmlspecialchars($buildComponentCounts[$post['buildId']]['types']); ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="forum-stats">
                                        <div class="stat-item">
                                            <strong><?php echo $post['comment_count']; ?></strong>
                                            <small>Komentářů</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Stránkování -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1&sort=<?php echo $sortBy; ?>&search=<?php echo urlencode($searchQuery); ?>">První</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sortBy; ?>&search=<?php echo urlencode($searchQuery); ?>">Předchozí</a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sortBy; ?>&search=<?php echo urlencode($searchQuery); ?>">Další</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $totalPages; ?>&sort=<?php echo $sortBy; ?>&search=<?php echo urlencode($searchQuery); ?>">Poslední</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <style>
        .forum-post-card {
            transition: all 0.3s ease;
        }
        .forum-post-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
        }
        .forum-stats {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            background: #f9fafb;
            border-radius: 5px;
        }
        .stat-item strong {
            font-size: 1.5rem;
            color: #0066cc;
        }
        .stat-item small {
            color: #6c757d;
            font-size: 0.85rem;
        }
    </style>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>