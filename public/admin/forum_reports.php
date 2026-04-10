<?php
/**
 * Správa nahlášení na fóru
 * 
 * Zobrazuje nahlášené příspěvky a komentáře pro kontrolu.
 * Admin/moderátor je může vyřešit nebo zamítnout.
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Kontrola přihlášení a role (admin nebo moderátor)
if (!isset($_SESSION['user_id']) || !in_array(($_SESSION['roleId'] ?? 1), [2, 3])) {
    header('Location: /dmp/public/login.php');
    exit;
}

$status = $_GET['status'] ?? 'pending';
$page = (int)($_GET['page'] ?? 1);
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Získání počtu hlášení podle stavu
$countStmt = $pdo->prepare('SELECT status, COUNT(*) as count FROM forum_reports GROUP BY status');
$countStmt->execute();
$statusCounts = ['pending' => 0, 'resolved' => 0, 'dismissed' => 0];
foreach ($countStmt->fetchAll() as $row) {
    $statusCounts[$row['status']] = $row['count'];
}

// Získání hlášení
$reportsStmt = $pdo->prepare('
    SELECT 
        fr.id, fr.reason, fr.description, fr.status, fr.createdAt, fr.resolvedAt, fr.isPriority,
        fr.postId, fr.commentId, fr.adminNotes,
        CASE 
            WHEN fr.postId IS NOT NULL THEN fp.title
            WHEN fr.commentId IS NOT NULL THEN CONCAT("Comment: ", LEFT(fc.content, 50), "...")
            ELSE "Deleted"
        END as reported_content,
        u.username as reported_by,
        fp.userId as post_user_id,
        fc.userId as comment_user_id
    FROM forum_reports fr
    LEFT JOIN forum_posts fp ON fr.postId = fp.id
    LEFT JOIN forum_comments fc ON fr.commentId = fc.id
    LEFT JOIN users u ON fr.reportedByUserId = u.id
    WHERE fr.status = ?
    ORDER BY fr.isPriority DESC, fr.createdAt DESC
    LIMIT ? OFFSET ?
');
$reportsStmt->execute([$status, $perPage, $offset]);
$reports = $reportsStmt->fetchAll();

$totalReports = $statusCounts[$status];
$totalPages = ceil($totalReports / $perPage);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hlášení z fóra - Admin panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        html { background: linear-gradient(135deg, #f9fafb 0%, #e2e8e2 100%); }
        body { min-height: 100vh; }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
    
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 mb-4">Správa hlášení z fóra</h1>
            </div>
        </div>

        <!-- Karty stavů -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h5 class="card-title">Nevyřešené</h5>
                        <h2 class="text-warning"><?php echo $statusCounts['pending']; ?></h2>
                        <a href="?status=pending" class="btn btn-sm btn-outline-warning">Zobrazit</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Vyřešené</h5>
                        <h2 class="text-success"><?php echo $statusCounts['resolved']; ?></h2>
                        <a href="?status=resolved" class="btn btn-sm btn-outline-success">Zobrazit</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title">Zamítnuté</h5>
                        <h2 class="text-info"><?php echo $statusCounts['dismissed']; ?></h2>
                        <a href="?status=dismissed" class="btn btn-sm btn-outline-info">Zobrazit</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabulka hlášení -->
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-flag"></i>
                    <?php 
                    $statusLabel = [
                        'pending' => 'Nevyřešené Hlášení',
                        'resolved' => 'Vyřešené Hlášení',
                        'dismissed' => 'Zamítnuté Hlášení'
                    ];
                    echo $statusLabel[$status] ?? 'All Reports';
                    ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($reports)): ?>
                    <div class="alert alert-info m-4">Nebyly nalezené žádné hlášení.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Obsah</th>
                                    <th>Důvod</th>
                                    <th>Nahlásil</th>
                                    <th>Datum</th>
                                    <th>Stav</th>
                                    <th>Akce</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars(mb_substr($report['reported_content'], 0, 50, 'UTF-8')); ?></strong>
                                            <?php if ($report['isPriority']): ?>
                                                <span class="badge bg-warning text-dark ms-1">⭐ PRIORITA</span>
                                            <?php endif; ?>
                                            <?php if ($status === 'pending'): ?>
                                                <?php if ($report['postId']): ?>
                                                    <a href="/dmp/public/forum_post.php?id=<?php echo $report['postId']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">Zobrazit</a>
                                                <?php elseif ($report['commentId']): ?>
                                                    <small class="text-muted">(Komentář)</small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($report['reason']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['reported_by']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($report['createdAt'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $report['status'] === 'pending' ? 'warning' : 
                                                    ($report['status'] === 'resolved' ? 'success' : 'info');
                                            ?>">
                                                <?php echo ucfirst($report['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#reportModal"
                                                    onclick="loadReport(<?php echo htmlspecialchars(json_encode($report)); ?>)">
                                                <i class="bi bi-zoom-in"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stránkování -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo $status; ?>&page=1">První</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?>">Předchozí</a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?>">Další</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo $status; ?>&page=<?php echo $totalPages; ?>">Poslední</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Modal detail hlášení -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail hlášení</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="reportActionForm" action="/dmp/api/forum/handle-report.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="reportId" name="reportId">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><strong>Důvod:</strong></label>
                            <p id="modalReason" class="form-control-plaintext"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Popis:</strong></label>
                            <p id="modalDescription" class="form-control-plaintext" style="white-space: pre-wrap;"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Nahlášený obsah:</strong></label>
                            <p id="modalContent" class="form-control-plaintext"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Aktuální stav:</strong></label>
                            <p id="modalStatus" class="form-control-plaintext"></p>
                        </div>
                        <div class="mb-3">
                            <label for="adminNotes" class="form-label"><strong>Poznámky:</strong></label>
                            <textarea class="form-control" id="adminNotes" name="adminNotes" rows="3" 
                                      placeholder="Přidat poznámky k hlášení..."></textarea>
                        </div>
                        <?php if ($status === 'pending'): ?>
                            <div class="mb-3">
                                <label class="form-label"><strong>Akce:</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="action-hide" 
                                           value="hide-content" required>
                                    <label class="form-check-label" for="action-hide">
                                        Skrýt nahlášený obsah a označit jako vyřešené
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="action-resolve" 
                                           value="resolve">
                                    <label class="form-check-label" for="action-resolve">
                                        Vyřešit (bez zásahu do obsahu)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="action-dismiss" 
                                           value="dismiss">
                                    <label class="form-check-label" for="action-dismiss">
                                        Zamítnout hlášení
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavřít</button>
                        <?php if ($status === 'pending'): ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Provést akci
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
    
    <script>
        function loadReport(report) {
            document.getElementById('reportId').value = report.id;
            document.getElementById('modalReason').textContent = report.reason;
            document.getElementById('modalDescription').textContent = report.description || '(Žádný popis)';
            document.getElementById('modalContent').textContent = report.reported_content;
            document.getElementById('modalStatus').textContent = report.status.charAt(0).toUpperCase() + report.status.slice(1);
            document.getElementById('adminNotes').value = report.adminNotes || '';
        }

        // Odeslání formuláře akce hlášení
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.id === 'reportActionForm') {
                e.preventDefault();
                
                const form = e.target;
                const reportId = document.getElementById('reportId').value;
                const adminNotes = document.getElementById('adminNotes').value;
                const action = document.querySelector('input[name="action"]:checked');
                const csrfInput = document.querySelector('input[name="csrf_token"]');
                
                if (!reportId) {
                    alert('ID hlášení chybí');
                    return;
                }
                
                if (!action) {
                    alert('Prosím vyberte akci');
                    return;
                }
                
                if (!csrfInput) {
                    alert('CSRF token chybí');
                    return;
                }
                
                const csrfToken = csrfInput.value;
                const actionUrl = form.getAttribute('action');
                
                console.log('DEBUG - Form submission:', {
                    reportId: reportId,
                    action: action.value,
                    adminNotes: adminNotes,
                    csrfToken: csrfToken.substring(0, 10) + '...',
                    actionUrl: actionUrl
                });
                
                // Vytvoření FormData
                const formData = new FormData();
                formData.append('reportId', reportId);
                formData.append('action', action.value);
                formData.append('adminNotes', adminNotes);
                formData.append('csrf_token', csrfToken);
                
                console.log('DEBUG - FormData contents:', {
                    reportId: formData.get('reportId'),
                    action: formData.get('action'),
                    adminNotes: formData.get('adminNotes'),
                    csrf_token: (formData.get('csrf_token') || '').substring(0, 10) + '...'
                });
                
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('DEBUG - Response received:', {
                        status: response.status,
                        statusText: response.statusText,
                        contentType: response.headers.get('content-type')
                    });
                    return response.text().then(text => {
                        console.log('DEBUG - Response text:', text.substring(0, 200));
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                        }
                        return text;
                    });
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert('Akce hlášení byla úspěšně dokončena');
                            const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
                            if (modal) modal.hide();
                            location.reload();
                        } else {
                            alert('Chyba: ' + (data.message || 'Neznámá chyba'));
                        }
                    } catch (e) {
                        console.error('Chyba při parsování JSON:', e, 'Text byl:', text);
                        alert('Chyba při parsování odpovědi: ' + e.message);
                    }
                })
                .catch(error => {
                    console.error('DEBUG - Fetch error:', error);
                    alert('An error occurred: ' + error.message);
                });
            }
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
