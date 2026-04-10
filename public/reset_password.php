<?php
/**
 * Stránka pro resetování hesla
 * 
 * Ověřuje reset token z URL, zobrazuje formulář pro nové heslo.
 * Po odeslání validuje a aktualizuje hashované heslo v databázi.
 */
session_start();
require_once(__DIR__ . '/../db/connection.php');
require_once(__DIR__ . '/../includes/csrf.php');

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$valid_token = false;

// Validace tokenu a načtení uživatele
if (!empty($token) && !empty($email)) {
    try {
        $stmt = $pdo->prepare('
            SELECT id, password_reset_token_expires 
            FROM users 
            WHERE email = ? AND password_reset_token = ?
        ');
        $stmt->execute([$email, $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (strtotime($user['password_reset_token_expires']) >= time()) {
                $valid_token = true;
            } else {
                $errors[] = 'Reset link has expired. <a href="/dmp/public/forgot_password.php">Request a new one</a>';
            }
        } else {
            $errors[] = 'Invalid reset link.';
        }
    } catch (Exception $e) {
        error_log("Token validation error: " . $e->getMessage());
        $errors[] = 'An error occurred.';
    }
}

// Zresetování hesla přes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $token = $_POST['token'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password)) $errors[] = "Password is required.";
        if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
        if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

        if (empty($errors)) {
            try {
                // Ověříme znovu token a načteme uživatele
                $stmt = $pdo->prepare('
                    SELECT id, password_reset_token_expires 
                    FROM users 
                    WHERE email = ? AND password_reset_token = ?
                ');
                $stmt->execute([$email, $token]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $errors[] = 'Invalid reset link.';
                } elseif (strtotime($user['password_reset_token_expires']) < time()) {
                    $errors[] = 'Reset link has expired.';
                } else {
                    // Aktualizace hesla
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('
                        UPDATE users 
                        SET password = ?, password_reset_token = NULL, password_reset_token_expires = NULL 
                        WHERE id = ?
                    ');
                    
                    if ($stmt->execute([$hashed, $user['id']])) {
                        $success = 'Password reset successfully! You can now <a href="/dmp/public/login.php">login with your new password</a>.';
                        $valid_token = false;
                    } else {
                        $errors[] = 'Database error. Please try again.';
                    }
                }
            } catch (Exception $e) {
                error_log("Reset password error: " . $e->getMessage());
                $errors[] = 'An error occurred.';
            }
        }
    }
}
?>

<?php include_once('../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset hesla - PC konfigurator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4 text-center">Zresetujte si heslo</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($valid_token && empty($success)): ?>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">Nové heslo</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">Minimálně 8 znaků.</small>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Potvrzení hesla</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Resetovat heslo</button>

                <p class="mt-3 text-center"><a href="index.php">Zpět na hlavní stránku</a></p>
            </form>
            <?php elseif (empty($success)): ?>
                <div class="alert alert-warning text-center">
                    Neplatný nebo chybějící odkaz pro resetování. <a href="/dmp/public/forgot_password.php">Požádejte o nový</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
