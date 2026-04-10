<?php
/**
 * Dashboard uživatele
 * 
 * Zobrazuje přehled uživatelského účtu: uložené sestavy, nastavení profilu,
 * změna hesla a úroveň předplatného. Vyžaduje přihlášení.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';

// Přesměrování nepřihlášeného uživatele
if (!isset($_SESSION['user_id'])) {
    header("Location: /dmp/public/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$roleId = $_SESSION['roleId'] ?? 1;

$stmt = $pdo->prepare("SELECT id, username, email, password, subscription, createdAt FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$updateErrors = [];
$updateSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $updateErrors[] = 'Neplatný požadavek. Zkuste to znovu.';
    } else {
        $action = $_POST['action'] ?? '';


        if ($action === 'update_username') {
            $newUsername = trim($_POST['new_username'] ?? '');

            if (empty($newUsername)) {
                $updateErrors[] = 'Uživatelské jméno nemůže být prázdné.';
            } elseif (strlen($newUsername) < 3) {
                $updateErrors[] = 'Uživatelské jméno musí mít alespoň 3 znaky.';
            } elseif (strlen($newUsername) > 50) {
                $updateErrors[] = 'Uživatelské jméno nesmí přesáhnout 50 znaků.';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$newUsername, $userId]);
                if ($stmt->fetch()) {
                    $updateErrors[] = 'Uživatelské jméno je již obsazeno.';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $stmt->execute([$newUsername, $userId]);
                    $_SESSION['username'] = $newUsername;
                    $user['username'] = $newUsername;
                    $updateSuccess = true;
                }
            }
        }

  
        if ($action === 'update_email') {
            $newEmail = trim($_POST['new_email'] ?? '');

            if (empty($newEmail)) {
                $updateErrors[] = 'E-mail nemůže být prázdný.';
            } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $updateErrors[] = 'Neplatný formát e-mailu.';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$newEmail, $userId]);
                if ($stmt->fetch()) {
                    $updateErrors[] = 'E-mail je již používán.';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $stmt->execute([$newEmail, $userId]);
                    $user['email'] = $newEmail;
                    $updateSuccess = true;
                }
            }
        }

        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $updateErrors[] = 'Všechna pole hesla jsou povinná.';
            } elseif (!password_verify($currentPassword, $user['password'] ?? '')) {
                $updateErrors[] = 'Aktuální heslo je nesprávné.';
            } elseif (strlen($newPassword) < 6) {
                $updateErrors[] = 'Nové heslo musí mít alespoň 6 znaků.';
            } elseif ($newPassword !== $confirmPassword) {
                $updateErrors[] = 'Nová hesla se neshodují.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                $updateSuccess = true;
            }
        }

        if ($action === 'toggle_build_visibility') {
            $buildId = $_POST['build_id'] ?? null;
            $isPublic = $_POST['is_public'] ?? null;

            if ($buildId && $isPublic !== null) {
                // Ověří vlastnictví sestavy
                $stmt = $pdo->prepare("SELECT id FROM builds WHERE id = ? AND userId = ?");
                $stmt->execute([$buildId, $userId]);
                if ($stmt->fetch()) {
                    $isPublicBool = $isPublic === '1' ? 1 : 0;
                    $stmt = $pdo->prepare("UPDATE builds SET isPublic = ? WHERE id = ? AND userId = ?");
                    $stmt->execute([$isPublicBool, $buildId, $userId]);
                    $updateSuccess = true;
                } else {
                    $updateErrors[] = 'Sestava nebyla nalezena nebo nemáte oprávnění ji upravovat.';
                }
            }
        }
    }
}


$stmt = $pdo->prepare("SELECT id, name, description, isPublic, createdAt FROM builds WHERE userId = ? ORDER BY createdAt DESC LIMIT 5");
$stmt->execute([$userId]);
$recentBuilds = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles = [1 => 'Uživatel', 2 => 'Admin', 3 => 'Moderátor'];
$roleName = $roles[$roleId] ?? 'Neznámá role';
$loggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    
    <style>

        body {
            background: #f5f5f5;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 4px solid #618B4A;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #618B4A, #4a6a38);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin-right: 1.5rem;
        }

        .profile-info h2 {
            margin: 0;
            color: #0a0908;
        }

        .profile-info p {
            margin: 0.25rem 0;
            color: #666;
            font-size: 0.95rem;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #0a0908;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #618B4A;
            padding-bottom: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #618B4A;
            box-shadow: 0 0 0 0.2rem rgba(97, 139, 74, 0.15);
        }

        .btn-update {
            background: #618B4A;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-update:hover {
            background: #4a6a38;
            color: white;
        }

        .build-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 3px solid #618B4A;
            gap: 1rem;
        }

        .visibility-select {
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
        }

        .visibility-select:focus {
            outline: none;
            border-color: #618B4A;
            box-shadow: 0 0 0 0.2rem rgba(97, 139, 74, 0.15);
        }

        .build-info h5 {
            margin: 0 0 0.25rem 0;
            color: #0a0908;
        }

        .build-info p {
            margin: 0;
            font-size: 0.85rem;
            color: #666;
        }

        .build-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .badge-public {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-private {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .tab-button {
            background: white;
            border: 1px solid #ddd;
            padding: 0.75rem 1.5rem;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            background: #618B4A;
            color: white;
            border-color: #618B4A;
        }

        .tab-container {
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>

<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="dashboard-container">
    <div class="profile-card">
        <div class="profile-header">
            <div style="display: flex; align-items: center;">
                <div class="profile-avatar"><?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?></div>
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user['username'] ?? '') ?></h2>
                    <p>📧 <?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <p>👤 Role: <strong><?= htmlspecialchars($roleName) ?></strong></p>
                    <p>📅 Členem od <?= date('M d, Y', strtotime($user['createdAt'] ?? 'now')) ?></p>
                    <?php
                    // Získá název plánu a počet sestav
                    $subscription = (int)($user['subscription'] ?? 1);
                    
                    // Ověří, že plán je v platném rozsahu (1-3)
                    if (!in_array($subscription, [1, 2, 3])) {
                        $subscription = 1;
                    }
                    
                    $tierNames = [1 => 'Zdarma', 2 => 'Pro', 3 => 'Premium'];
                    $tierName = $tierNames[$subscription] ?? 'Zdarma';
                    $tierColors = [1 => '#6c757d', 2 => '#667eea', 3 => '#ffc107'];
                    $tierColor = $tierColors[$subscription] ?? '#6c757d';
                    
                    $buildStmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM builds WHERE userId = ?');
                    $buildStmt->execute([$userId]);
                    $buildCount = (int)($buildStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
                    
                    $limits = [1 => 2, 2 => 6, 3 => 999999];
                    $limit = $limits[$subscription] ?? 2;
                    ?>
                    <p style="margin-top: 0.75rem;">
                        💎 Plán: <strong style="color: <?= $tierColor ?>; font-size: 1.1rem;"><?= htmlspecialchars($tierName) ?></strong>
                        <br><small style="color: #666;">Sestav: <?= $buildCount ?>/<?= $limit === 999999 ? '∞' : $limit ?></small>
                    </p>
                </div>
            </div>
            <div style="text-align: right;">
                <a href="/dmp/public/upgrade.php" class="btn btn-primary" style="margin-bottom: 1rem; background: #667eea; border: none;">🚀 Upgradovat</a>
                <br>
                <a href="/dmp/public/logout.php" class="btn btn-outline-danger">Odhlásit se</a>
            </div>
        </div>
    </div>

    <?php if ($updateSuccess): ?>
        <div class="alert alert-success">
            ✓ Profil byl úspěšně aktualizován!
        </div>
    <?php endif; ?>

    <?php if (!empty($updateErrors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($updateErrors as $error): ?>
                <p class="mb-1">✗ <?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="section-card">
        <h3 class="section-title">Nastavení účtu</h3>

        <div class="tab-container">
            <button class="tab-button active" onclick="switchTab('edit-username')">Změnit uživatelské jméno</button>
            <button class="tab-button" onclick="switchTab('edit-email')">Změnit e-mail</button>
            <button class="tab-button" onclick="switchTab('change-password')">Změnit heslo</button>
        </div>

        <div id="edit-username" class="tab-content active">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_username">

                <div class="form-group">
                    <label class="form-label">Aktuální uživatelské jméno</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="new_username" class="form-label">Nové uživatelské jméno</label>
                    <input type="text" id="new_username" name="new_username" class="form-control" placeholder="Zadejte nové uživatelské jméno" required>
                    <small class="text-muted">3-50 znaků, písmena, čísla a podtržítka.</small>
                </div>

                <button type="submit" class="btn btn-update">Aktualizovat uživatelské jméno</button>
            </form>
        </div>

        <div id="edit-email" class="tab-content">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_email">

                <div class="form-group">
                    <label class="form-label">Aktuální e-mail</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="new_email" class="form-label">Nový e-mail</label>
                    <input type="email" id="new_email" name="new_email" class="form-control" placeholder="Zadejte novou e-mailovou adresu" required>
                </div>

                <button type="submit" class="btn btn-update">Aktualizovat e-mail</button>
            </form>
        </div>

        <div id="change-password" class="tab-content">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="current_password" class="form-label">Aktuální heslo</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Zadejte aktuální heslo" required>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">Nové heslo</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Zadejte nové heslo" required>
                    <small class="text-muted">Minimálně 6 znaků.</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Potvrďte nové heslo</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Potvrďte nové heslo" required>
                </div>

                <button type="submit" class="btn btn-update">Aktualizovat heslo</button>
            </form>
        </div>
    </div>

    <div class="section-card">
        <h3 class="section-title">Vaše nedávné sestavy</h3>
        <?php if (empty($recentBuilds)): ?>
            <p class="text-muted text-center py-5">
                Žádné sestavy zatím. <a href="/dmp/public/configurator.php" class="text-decoration-none">Začněte sestavovat nyní!</a>
            </p>
        <?php else: ?>
            <?php foreach ($recentBuilds as $build): ?>
                <div class="build-item">
                    <div class="build-info">
                        <h5><?= htmlspecialchars($build['name']) ?></h5>
                        <p style="margin: 0; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;"><?= htmlspecialchars($build['description'] ?? 'Žádný popis') ?></p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <form method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle_build_visibility">
                            <input type="hidden" name="build_id" value="<?= $build['id'] ?>">
                            <select name="is_public" class="visibility-select" onchange="this.form.submit()">
                                <option value="0" <?= !$build['isPublic'] ? 'selected' : '' ?>>🔒 Soukromé</option>
                                <option value="1" <?= $build['isPublic'] ? 'selected' : '' ?>>🌐 Veřejné</option>
                            </select>
                        </form>
                        <a href="/dmp/public/build.php?id=<?= $build['id'] ?>" class="btn btn-sm btn-outline-primary">Zobrazit</a>
                        <a href="/dmp/public/build_edit.php?id=<?= $build['id'] ?>" class="btn btn-sm btn-outline-success">Upravit</a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBuild(<?= $build['id'] ?>, '<?= htmlspecialchars($build['name']) ?>')">Smazat</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="/dmp/public/builds.php" class="btn btn-outline-secondary mt-3">Zobrazit všechny sestavy →</a>
    </div>

</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>


<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }

    function deleteBuild(buildId, buildName) {
        if (!confirm(`Opravdu chcete smazat sestavu "${buildName}"? Tuto akci nelze vrátit.`)) {
            return;
        }

        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '<?= csrf_token() ?>';

        fetch('/dmp/api/builds/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'build_id': buildId,
                'csrf_token': csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sestava byla úspěšně smazána.');
                location.reload();
            } else {
                alert('Chyba: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Chyba sítě. Prosím zkuste znovu.');
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>