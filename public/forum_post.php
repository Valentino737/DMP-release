<?php
/**
 * Detail příspěvku na fóru
 * 
 * Zobrazuje příspěvek, komentáře a formulář pro přidání komentáře.
 * Umožňuje nahlášení příspěvku a mazání (vlastník/admin/moderátor).
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';

$postId = (int)($_GET['id'] ?? 0);

if ($postId === 0) {
    header('Location: /dmp/public/forum.php');
    exit;
}

// Vybere daný příspěvek, autora a případně přiřazenou sestavu
$postStmt = $pdo->prepare('SELECT fp.id, fp.title, fp.content, fp.createdAt, fp.updatedAt, fp.userId, 
                                  fp.isVisible, fp.buildId, u.username, u.id as author_id,
                                  b.name as build_name, b.id as build_id, b.description as build_description
                          FROM forum_posts fp
                          JOIN users u ON fp.userId = u.id
                          LEFT JOIN builds b ON fp.buildId = b.id
                          WHERE fp.id = ?');
$postStmt->execute([$postId]);
$post = $postStmt->fetch();

if (!$post || !$post['isVisible']) {
    header('HTTP/1.0 404 Not Found');
    echo '404 - Post not found';
    exit;
}

// Získá komponenty sestavy, pokud je nastaven buildId
$buildComponents = [];
if (!empty($post['build_id'])) {
    try {
        $buildStmt = $pdo->prepare('SELECT up.id, up.partId, p.name, p.price, p.typeId, t.name_short as type_name,
                                           c.core_count, c.thread_count, c.core_clock, c.tdp as cpu_tdp,
                                           g.memory as gpu_memory, g.chipset, g.core_clock as gpu_core_clock, g.tdp as gpu_tdp,
                                           r.speed as ram_speed, r.modules,
                                           m.socket, m.form_factor,
                                           ps.wattage, ps.efficiency,
                                           pc.type as case_type,
                                           s.capacity, s.interface
                                    FROM used_parts up
                                    LEFT JOIN parts p ON up.partId = p.id
                                    LEFT JOIN type t ON p.typeId = t.id
                                    LEFT JOIN cpu c ON p.partId_cpu = c.id
                                    LEFT JOIN gpu g ON p.partId_gpu = g.id
                                    LEFT JOIN ram r ON p.partId_ram = r.id
                                    LEFT JOIN motherboard m ON p.partId_mboard = m.id
                                    LEFT JOIN psu ps ON p.partId_psu = ps.id
                                    LEFT JOIN pc_case pc ON p.partId_case = pc.id
                                    LEFT JOIN storage s ON p.partId_storage = s.id
                                    WHERE up.buildId = ?
                                    ORDER BY t.name_short ASC');
        $buildStmt->execute([$post['build_id']]);
        $buildComponents = $buildStmt->fetchAll();
    } catch (PDOException $e) {
        // Pokud tabulky komponent neexistují, jednoduše přeskočí
        $buildComponents = [];
    }
}

$errors = [];

// Zpracování odeslání komentáře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $errors[] = "Pro komentování musíte být přihlášeni.";
    } elseif (!csrf_validate()) {
        $errors[] = "Neplatný požadavek. Zkuste to prosím znovu.";
    } else {
        $commentContent = trim($_POST['comment'] ?? '');
        
        if (empty($commentContent)) {
            $errors[] = "Komentář nesmí být prázdný.";
        } elseif (strlen($commentContent) < 3) {
            $errors[] = "Komentář musí mít alespoň 3 znaky.";
        } else {
            try {
                $insertCommentStmt = $pdo->prepare('INSERT INTO forum_comments (postId, userId, content, isVisible, createdAt, updatedAt)
                                                   VALUES (?, ?, ?, TRUE, NOW(), NOW())');
                $insertCommentStmt->execute([$postId, $_SESSION['user_id'], $commentContent]);
                
                // Přesměrování pro obnovení stránky a zobrazení nového komentáře
                header('Location: /dmp/public/forum_post.php?id=' . $postId);
                exit;
            } catch (PDOException $e) {
                $errors[] = "Při odesílání komentáře došlo k chybě.";
            }
        }
    }
}

// Získá komentáře
$commentsStmt = $pdo->prepare('SELECT fc.id, fc.content, fc.createdAt, fc.userId, u.username
                              FROM forum_comments fc
                              JOIN users u ON fc.userId = u.id
                              WHERE fc.postId = ? AND fc.isVisible = TRUE
                              ORDER BY fc.createdAt ASC');
$commentsStmt->execute([$postId]);
$comments = $commentsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Fórum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        .build-components {
            background: #fff;
            border-radius: 8px;
        }
        .component-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        .component-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-color: #0066cc;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container my-5" style="flex: 1 0 auto">
        <div class="row">
            <div class="col-lg-8">
                <!-- Hlavička příspěvku -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <small class="text-muted">
                                    Přidal/a <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                                    dne <?php echo date('d. m. Y H:i', strtotime($post['createdAt'])); ?>
                                </small>
                                <?php if ($post['createdAt'] !== $post['updatedAt']): ?>
                                    <br><small class="text-muted">(Upraveno dne <?php echo date('d. m. Y H:i', strtotime($post['updatedAt'])); ?>)</small>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($_SESSION['user_id']) && 
                                      ($_SESSION['user_id'] == $post['userId'] || in_array($_SESSION['roleId'], [2, 3]))): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if ($_SESSION['user_id'] == $post['userId']): ?>
                                            <li><a class="dropdown-item" href="/dmp/public/forum_edit.php?id=<?php echo $postId; ?>">
                                                <i class="bi bi-pencil"></i> Upravit
                                            </a></li>
                                        <?php endif; ?>
                                        <?php if (in_array($_SESSION['roleId'], [2, 3]) && $_SESSION['user_id'] != $post['userId']): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deletePost(<?php echo $postId; ?>)">
                                                <i class="bi bi-trash"></i> Smazat (Moderátor)
                                            </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($post['build_name'])): ?>
                            <div class="card bg-light mb-3 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <strong><i class="bi bi-pc-display"></i> Přiřazená sestava: <?php echo htmlspecialchars($post['build_name']); ?></strong>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($post['build_description'])): ?>
                                        <p class="mb-3"><em><?php echo nl2br(htmlspecialchars($post['build_description'])); ?></em></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($buildComponents)): ?>
                                        <div class="build-components">
                                            <h6 class="mb-3">Komponenty:</h6>
                                            <div class="row g-2">
                                                <?php 
                                                $componentsByType = [];
                                                foreach ($buildComponents as $comp) {
                                                    $type = $comp['type_name'] ?? 'Unknown';
                                                    if (!isset($componentsByType[$type])) {
                                                        $componentsByType[$type] = [];
                                                    }
                                                    $componentsByType[$type][] = $comp;
                                                }
                                                
                                                foreach ($componentsByType as $type => $components): 
                                                ?>
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="component-card">
                                                            <small class="text-muted text-uppercase fw-bold"><?php echo htmlspecialchars($type); ?></small>
                                                            <?php foreach ($components as $comp): ?>
                                                                <div class="mt-2">
                                                                    <strong><?php echo htmlspecialchars($comp['name']); ?></strong>
                                                                    <div class="component-specs">
                                                                        <?php if ($type === 'CPU' && !empty($comp['core_count'])): ?>
                                                                            <small class="d-block text-muted">
                                                                                <?php echo $comp['core_count']; ?>C/<?php echo $comp['thread_count']; ?>T • 
                                                                                <?php echo number_format($comp['core_clock'], 1); ?> GHz
                                                                                <?php if (!empty($comp['cpu_tdp'])): echo "• {$comp['cpu_tdp']}W"; endif; ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($type === 'GPU' && !empty($comp['gpu_memory'])): ?>
                                                                            <small class="d-block text-muted">
                                                                                <?php echo $comp['gpu_memory']; ?> GB • <?php echo htmlspecialchars($comp['chipset']); ?>
                                                                                <?php if (!empty($comp['gpu_tdp'])): echo "• {$comp['gpu_tdp']}W"; endif; ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($type === 'RAM' && !empty($comp['ram_speed'])): ?>
                                                                            <small class="d-block text-muted">
                                                                                <?php echo htmlspecialchars($comp['modules']); ?> • <?php echo $comp['ram_speed']; ?> MHz
                                                                            </small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($type === 'Motherboard' && !empty($comp['socket'])): ?>
                                                                            <small class="d-block text-muted">
                                                                                <?php echo htmlspecialchars($comp['socket']); ?> • <?php echo htmlspecialchars($comp['form_factor']); ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($type === 'PSU' && !empty($comp['wattage'])): ?>
                                                                            <small class="d-block text-muted">
                                                                                <?php echo $comp['wattage']; ?>W • <?php echo htmlspecialchars($comp['efficiency']); ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($type === 'Case' && !empty($comp['case_type'])): ?>
                                                                            <small class="d-block text-muted">
                                                                                <?php echo htmlspecialchars($comp['case_type']); ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($type === 'Storage' && !empty($comp['capacity'])): ?>
                                                                            <small class="d-block text-muted">
                                                                                <?php echo $comp['capacity']; ?> GB • <?php echo htmlspecialchars($comp['interface']); ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <small class="d-block text-success fw-bold mt-1">$<?php echo number_format((float)$comp['price'], 2); ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Žádné komponenty v této sestavě.</p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <a href="/dmp/public/build.php?id=<?php echo $post['build_id']; ?>&ref=forum_post&post_id=<?php echo $postId; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Zobrazit celou sestavu
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Obsah příspěvku -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="post-content" style="line-height: 1.8; color: #333;">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Sekce komentářů -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Komentáře (<?php echo count($comments); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($comments)): ?>
                            <p class="text-muted">Zatím žádné komentáře. Buďte první!</p>
                        <?php else: ?>
                            <div class="comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="card mb-3 border-start border-primary" style="border-left-width: 4px;">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                                    <small class="text-muted">
                                                        • <?php echo date('d. m. Y H:i', strtotime($comment['createdAt'])); ?>
                                                    </small>
                                                </div>
                                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['userId']): ?>
                                                    <a href="#" class="text-danger small" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                                        <i class="bi bi-trash"></i> Smazat
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sekce přidání komentáře -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Přidat komentář</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?php echo htmlspecialchars($error); ?></div>
                                    <?php endforeach; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment" rows="4" 
                                              placeholder="Podělte se o své myšlenky..." required></textarea>
                                    <small class="text-muted">Buďte slušní a konstruktivní</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Odeslat komentář
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <a href="/dmp/public/login.php" class="alert-link">Přihlaste se</a> pro zapojení do diskuse!
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar příspěvku -->
            <div class="col-lg-4">
                <!-- Karta pro nahlášení příspěvku -->
                <div class="card shadow-sm mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="bi bi-flag"></i> Nahlásit příspěvek</h6>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <p class="small mb-3">Pokud se domníváte, že tento příspěvek porušuje pravidla komunity, nahlaste ho moderátorům.</p>
                            <button class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#reportModal">
                                <i class="bi bi-flag"></i> Nahlásit příspěvek
                            </button>
                        <?php else: ?>
                            <p class="small text-muted">Pro nahlášení obsahu se musíte přihlásit.</p>
                            <a href="/dmp/public/login.php" class="btn btn-outline-danger btn-sm w-100">Přihlásit se</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistiky příspěvku -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Informace o příspěvku</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong>Autor:</strong>
                                <a href="/dmp/public/profile.php?id=<?php echo $post['author_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($post['username']); ?>
                                </a>
                            </li>
                            <li class="mb-2">
                                <strong>Komentáře:</strong>
                                <?php echo count($comments); ?>
                            </li>
                            <li>
                                <strong>Vytvořeno:</strong>
                                <?php echo date('d. m. Y', strtotime($post['createdAt'])); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="/dmp/public/forum.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Zpět na fórum
            </a>
        </div>
    </div>

    <!-- Modal pro nahlášení příspěvku -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nahlásit příspěvek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="reportForm" action="/dmp/api/forum/report-post.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="postId" value="<?php echo $postId; ?>">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Důvod nahlášení</label>
                            <select class="form-select" id="reason" name="reason" required>
                                <option value="">-- Vyberte důvod --</option>
                                <option value="inappropriate-content">Nevhodný obsah</option>
                                <option value="harassment">Obtěžování nebo šikana</option>
                                <option value="spam">Spam</option>
                                <option value="misinformation">Dezinformace</option>
                                <option value="off-topic">Mimo téma</option>
                                <option value="other">Jiné</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Doplňující informace (volitelné)</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Uveďte další podrobnosti..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-flag"></i> Odeslat nahlášení
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
    
    <script>
        const csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8") ?>';
        function deletePost(postId) {
            if (confirm('Opravdu chcete smazat tento příspěvek? Tuto akci nelze vrátit.')) {
                fetch('/dmp/api/forum/delete-post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + postId + '&csrf_token=' + encodeURIComponent(csrfToken)
                })
                .then(response => {
                    window.location.href = '/dmp/public/forum.php';
                })
                .catch(err => alert('Chyba: ' + err));
            }
        }

        function deleteComment(commentId) {
            if (confirm('Opravdu chcete smazat tento komentář?')) {
                fetch('/dmp/api/forum/delete-comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + commentId + '&csrf_token=' + encodeURIComponent(csrfToken)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }

        // Zpracování odeslání formuláře pro nahlášení příspěvku
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Nahlášení bylo úspěšně odesláno. Děkujeme za pomoc s udržováním komunity!');
                    bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                } else {
                    alert('Chyba: ' + data.message);
                }
            })
            .catch(error => {
                alert('Došlo k chybě. Zkuste to prosím znovu.');
                console.error('Error:', error);
            });
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
