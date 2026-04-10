<?php
/**
 * Správa návrhů komponent od uživatelů
 * 
 * Admin může schválit, zamítnout nebo resetovat návrhy komponent.
 * Schválené návrhy se přidají do databáze. Pouze pro adminy.
 */
session_start();
require_once(__DIR__ . '/../../db/connection.php');
require_once(__DIR__ . '/../../includes/csrf.php');


if (($_SESSION['roleId'] ?? 1) !== 2) {
    header('Location: /dmp/public/index.php');
    exit;
}

$tab = $_GET['tab'] ?? 'pending';
$componentType = $_GET['type'] ?? '';


$sql = "
    SELECT cs.*, u.username, u.email,
           CASE 
               WHEN cs.reviewedBy IS NOT NULL THEN (SELECT username FROM users WHERE id = cs.reviewedBy)
               ELSE NULL
           END as reviewedByUsername
    FROM component_submissions cs
    JOIN users u ON cs.userId = u.id
    WHERE 1=1
";

$params = [];

if ($tab !== 'all') {
    $sql .= " AND cs.status = ?";
    $params[] = $tab;
}

if (!empty($componentType)) {
    $sql .= " AND cs.componentType = ?";
    $params[] = $componentType;
}

$sql .= " ORDER BY cs.isPriority DESC, cs.createdAt DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get component type counts
$countStmt = $pdo->query("
    SELECT status, COUNT(*) as count FROM component_submissions GROUP BY status
");
$counts = [];
foreach ($countStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $counts[$row['status']] = $row['count'];
}

$componentTypes = ['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'case', 'cooler'];
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Správa návrhů komponent - DMP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        html { background: linear-gradient(135deg, #f9fafb 0%, #e2e8e2 100%); }
        body { min-height: 100vh; }
        .submission-card {
            border-left: 4px solid #007bff;
        }
        .submission-card.approved {
            border-left-color: #28a745;
        }
        .submission-card.rejected {
            border-left-color: #dc3545;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #333;
        }
        .spec-item {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .spec-item strong {
            color: #495057;
        }
    </style>
</head>
<body>
<?php include_once(__DIR__ . '/../../includes/navbar.php'); ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">📋 Návrhy komponent</h1>
            <p class="text-muted">Kontrola a správa uživateli navržených komponent</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" href="?tab=pending">
                ⏳ Čekající <span class="badge bg-warning"><?= $counts['pending'] ?? 0 ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'approved' ? 'active' : '' ?>" href="?tab=approved">
                ✅ Schválené <span class="badge bg-success"><?= $counts['approved'] ?? 0 ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'rejected' ? 'active' : '' ?>" href="?tab=rejected">
                ❌ Zamítnuté <span class="badge bg-danger"><?= $counts['rejected'] ?? 0 ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'all' ? 'active' : '' ?>" href="?tab=all">
                📊 Vše <span class="badge bg-secondary"><?= array_sum($counts) ?></span>
            </a>
        </li>
    </ul>

    <!-- Filtr -->
    <div class="mb-3">
        <label for="typeFilter" class="form-label">Filtrovat dle typu komponenty:</label>
        <select id="typeFilter" class="form-select" style="max-width: 200px;">
            <option value="">Všechny typy</option>
            <?php foreach ($componentTypes as $type): ?>
                <option value="<?= $type ?>" <?= $componentType === $type ? 'selected' : '' ?>>
                    <?= ucfirst($type) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Seznam návrhů -->
    <div class="row">
        <?php if (empty($submissions)): ?>
            <div class="col">
                <div class="alert alert-light">Žádné návrhy nebyly nalezeny.</div>
            </div>
        <?php else: ?>
            <?php foreach ($submissions as $sub): ?>
                <div class="col-12 mb-3">
                    <div class="card submission-card <?= $sub['status'] ?>">
                        <div class="card-body">
                            <div class="row align-items-start">
                                <div class="col-md-6">
                                    <h5 class="card-title mb-1">
                                        <?= htmlspecialchars($sub['name']) ?>
                                        <?php if ($sub['isPriority']): ?>
                                            <span class="badge bg-warning text-dark ms-2">⭐ PRIORITA</span>
                                        <?php endif; ?>
                                        <span class="badge badge-<?= $sub['status'] ?> ms-2">
                                            <?= strtoupper($sub['status']) ?>
                                        </span>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <small>
                                            <?= ucfirst($sub['componentType']) ?> • 
                                            Od <strong><?= htmlspecialchars($sub['username']) ?></strong> (<?= htmlspecialchars($sub['email']) ?>) •
                                            Odesláno <?= date('d.m.Y H:i', strtotime($sub['createdAt'])) ?>
                                        </small>
                                    </p>
                                    
                                    <div class="mb-2">
                                        <?php if (!empty($sub['brand'])): ?>
                                            <div class="spec-item"><strong>Značka:</strong> <?= htmlspecialchars($sub['brand']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($sub['price'])): ?>
                                            <div class="spec-item"><strong>Cena:</strong> <?= number_format($sub['price'], 2) ?> Kč</div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($sub['specifications'])): ?>
                                        <?php $specs = json_decode($sub['specifications'], true); ?>
                                        <?php if (is_array($specs)): ?>
                                            <div class="mb-2">
                                                <small class="text-muted">Specifikace:</small>
                                                <div>
                                                    <?php foreach ($specs as $key => $value): ?>
                                                        <div class="spec-item">
                                                            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</strong>
                                                            <?= htmlspecialchars($value) ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($sub['status'] === 'rejected' && !empty($sub['rejectionReason'])): ?>
                                        <div class="alert alert-danger mt-2 mb-0" style="font-size: 0.9rem;">
                                            <strong>Důvod zamítnutí:</strong> <?= htmlspecialchars($sub['rejectionReason']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($sub['reviewedBy']): ?>
                                        <p class="text-muted mb-0 mt-2">
                                            <small>Zkontroloval <?= htmlspecialchars($sub['reviewedByUsername']) ?> dne <?= date('d.m.Y H:i', strtotime($sub['reviewedAt'])) ?></small>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 text-end">
                                    <?php if ($sub['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-success me-2" onclick="approveSubmission(<?= $sub['id'] ?>)">
                                            ✅ Schválit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectSubmission(<?= $sub['id'] ?>)">
                                            ❌ Zamítnout
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal zamítnutí -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">❌ Zamítnout návrh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="rejectionReason" class="form-label">Důvod zamítnutí:</label>
                    <textarea class="form-control" id="rejectionReason" rows="3" placeholder="Vysvětlete, proč je tato komponenta zamítnuta..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Zamítnout</button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8") ?>';
let rejectModal;
let currentRejectId;

document.addEventListener('DOMContentLoaded', function() {
    rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    
    document.getElementById('typeFilter').addEventListener('change', function() {
        const type = this.value;
        const url = new URL(window.location);
        if (!type) {
            url.searchParams.delete('type');
        } else {
            url.searchParams.set('type', type);
        }
        window.location = url;
    });
});

function approveSubmission(id) {
    if (!confirm('Schválit tento návrh komponenty?')) return;
    
    fetch('/dmp/api/components/admin-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            submissionId: id,
            action: 'approve',
            csrf_token: csrfToken
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Komponenta schválena a přidána do databáze!');
            location.reload();
        } else {
            alert('Error: ' + d.message);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

function rejectSubmission(id) {
    currentRejectId = id;
    document.getElementById('rejectionReason').value = '';
    rejectModal.show();
}

document.getElementById('confirmRejectBtn').addEventListener('click', function() {
    const reason = document.getElementById('rejectionReason').value.trim();
    
    if (!reason) {
        alert('Zadejte prosím důvod zamítnutí');
        return;
    }
    
    fetch('/dmp/api/components/admin-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            submissionId: currentRejectId,
            action: 'reject',
            reason: reason,
            csrf_token: csrfToken
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Návrh komponenty zamítnut');
            location.reload();
        } else {
            alert('Error: ' + d.message);
        }
    })
    .catch(e => alert('Error: ' + e.message));
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
