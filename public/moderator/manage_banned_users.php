<?php
/**
 * Správa zablokovaných uživatelů
 * 
 * Zobrazuje seznam banů s možností odblokovat uživatele.
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

// Načtení zablokovaných uživatelů
try {
    $stmt = $pdo->prepare('
        SELECT u.id, u.username, u.email, u.bannedAt, u.banReason, 
               admin.username as bannedByUsername
        FROM users u
        LEFT JOIN users admin ON u.bannedBy = admin.id
        WHERE u.is_banned = TRUE
        ORDER BY u.bannedAt DESC
    ');
    $stmt->execute();
    $bannedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Get banned users error: " . $e->getMessage());
    $bannedUsers = [];
}

    // Získání všech uživatelů pro možnost zablokování (neblokovaní, kromě admina)
try {
    $stmt = $pdo->prepare('
        SELECT u.id, u.username, u.email, u.roleId, r.name as roleName,
               COUNT(fp.id) as postCount, COUNT(fc.id) as commentCount
        FROM users u
        JOIN roles r ON u.roleId = r.id
        LEFT JOIN forum_posts fp ON u.id = fp.userId AND fp.isVisible = TRUE
        LEFT JOIN forum_comments fc ON u.id = fc.userId AND fc.isVisible = TRUE
        WHERE u.is_banned = FALSE
        GROUP BY u.id, u.username, u.email, u.roleId, r.name
        ORDER BY u.username
        LIMIT 100
    ');
    $stmt->execute();
    $activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Get active users error: " . $e->getMessage());
    $activeUsers = [];
}

?>

<?php include_once('../../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa zablokovaných - Panel moderátora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>

<?php include_once('../../includes/navbar.php'); ?>

<div class="container py-5">
    <div class="mb-4">
        <h2>🚫 Správa zablokovaných uživatelů</h2>
        <p class="text-muted">Zablokovat nebo odblokovat uživatele</p>
        <a href="/dmp/public/moderator/index.php" class="btn btn-secondary btn-sm">← Zpět na panel moderátora</a>
    </div>

    <!-- Přepínačka záložek -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="banned-tab" data-bs-toggle="tab" 
                    data-bs-target="#banned-panel" type="button" role="tab">
                Zablokovaní (<?= count($bannedUsers) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="active-tab" data-bs-toggle="tab" 
                    data-bs-target="#active-panel" type="button" role="tab">
                Aktivní uživatelé (<?= count($activeUsers) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Zablokovaní uživatelé -->
        <div class="tab-pane fade show active" id="banned-panel" role="tabpanel">
            <?php if (empty($bannedUsers)): ?>
                <div class="alert alert-info">
                    Žádní zablokovaní uživatelé.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Uživatelské jméno</th>
                                <th>E-mail</th>
                                <th>Důvod blokace</th>
                                <th>Zablokoval</th>
                                <th>Datum blokace</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bannedUsers as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($user['email']) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($user['banReason'] ?? 'Bez důvodu') ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($user['bannedByUsername'] ?? 'Neznámý') ?></small>
                                </td>
                                <td>
                                    <small><?= date('d. m. Y H:i', strtotime($user['bannedAt'])) ?></small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success" 
                                            onclick="unbanUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                        Odblokovat
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Aktivní uživatelé -->
        <div class="tab-pane fade" id="active-panel" role="tabpanel">
            <?php if (empty($activeUsers)): ?>
                <div class="alert alert-info">
                    Žádní aktivní uživatelé.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Uživatelské jméno</th>
                                <th>E-mail</th>
                                <th>Role</th>
                                <th>Příspěvky</th>
                                <th>Komentáře</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeUsers as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($user['email']) ?></small>
                                </td>
                                <td>
                                    <span class="badge <?= $user['roleId'] === 2 ? 'bg-danger' : ($user['roleId'] === 3 ? 'bg-warning' : 'bg-primary') ?>">
                                        <?= htmlspecialchars($user['roleName']) ?>
                                    </span>
                                </td>
                                <td><?= $user['postCount'] ?></td>
                                <td><?= $user['commentCount'] ?></td>
                                <td>
                                    <!-- Neumožňovat blokování adminů nebo sebe sama -->
                                    <?php if ($user['roleId'] !== 2 && $user['id'] !== $_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="showBanModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                            Zablokovat
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <?php if ($user['roleId'] === 2): ?>
                                                Chráněný (Admin)
                                            <?php else: ?>
                                                Nelze zablokovat sebe
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Blokace -->
<div class="modal fade" id="banModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Zablokovat uživatele</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="banForm">
                <div class="modal-body">
                    <input type="hidden" id="banUserId" name="userId">
                    <div class="mb-3">
                        <label for="banUsername" class="form-label">Uživatelské jméno:</label>
                        <input type="text" id="banUsername" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="banReason" class="form-label">Důvod blokace:</label>
                        <textarea class="form-control" id="banReason" name="banReason" rows="3" 
                                  placeholder="Zadejte důvod blokace (volitelné)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                    <button type="submit" class="btn btn-danger">Zablokovat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>


<script>
const csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8") ?>';
const banModal = new bootstrap.Modal(document.getElementById('banModal'), {});

function showBanModal(userId, username) {
    document.getElementById('banUserId').value = userId;
    document.getElementById('banUsername').value = username;
    document.getElementById('banReason').value = '';
    banModal.show();
}

function banUser(userId, reason) {
    fetch('/dmp/api/users/ban-user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'userId=' + userId + '&action=ban&banReason=' + encodeURIComponent(reason) + '&csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User banned successfully');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to ban user'));
        }
    })
    .catch(err => alert('Error: ' + err));
}

function unbanUser(userId, username) {
    if (confirm('Opravdu chcete odblokovat uživatele ' + username + '?')) {
        fetch('/dmp/api/users/ban-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'userId=' + userId + '&action=unban&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Uživatel úspěšně odblokován');
                location.reload();
            } else {
                alert('Chyba: ' + (data.message || 'Nepodařilo se odblokovat uživatele'));
            }
        })
        .catch(err => alert('Chyba: ' + err));
    }
}


document.getElementById('banForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const userId = document.getElementById('banUserId').value;
    const reason = document.getElementById('banReason').value;
    banModal.hide();
    banUser(userId, reason);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
