<?php
/**
 * Ověření e-mailové adresy
 * 
 * Zpracovává ověřovací odkaz z registračního e-mailu.
 * Kontroluje platnost tokenu a aktivuje uživatelský účet.
 */
session_start();
require_once(__DIR__ . '/../db/connection.php');

$error = '';
$success = '';

// Get token and email from URL
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (empty($token) || empty($email)) {
    $error = 'Neplatný ověřovací odkaz.';
} else {
    try {
        // Nalezení uživatele podle e-mailu a tokenu
        $stmt = $pdo->prepare('
            SELECT id, username, email_verified, verification_token_expires 
            FROM users 
            WHERE email = ? AND verification_token = ?
        ');
        $stmt->execute([$email, $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'Neplatný ověřovací odkaz nebo uživatel nebyl nalezen.';
        } elseif ($user['email_verified']) {
            $success = 'Váš e-mail již byl ověřen. Můžete se <a href="/dmp/public/login.php">přihlásit zde</a>.';
        } elseif (strtotime($user['verification_token_expires']) < time()) {
            $error = 'Platnost ověřovacího odkazu vypršela. <a href="/dmp/public/register.php">Zaregistrujte se znovu</a> pro získání nového ověřovacího e-mailu.';
        } else {
            // Ověření e-mailu
            $stmt = $pdo->prepare('
                UPDATE users 
                SET email_verified = 1, verification_token = NULL, verification_token_expires = NULL 
                WHERE id = ?
            ');
            
            if ($stmt->execute([$user['id']])) {
                $success = 'E-mail byl úspěšně ověřen! Nyní se můžete <a href="/dmp/public/login.php">přihlásit zde</a>.';
            } else {
                $error = 'Při ověřování e-mailu došlo k chybě. Zkuste to prosím znovu.';
            }
        }
    } catch (Exception $e) {
        error_log("Email verification error: " . $e->getMessage());
        $error = 'Při zpracování požadavku došlo k chybě.';
    }
}
?>

<?php include_once('../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Ověření e-mailu - DMP PC Konfigurátor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div style="text-align: center;">
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">✓ Úspěch!</h4>
                        <p><?= $success ?></p>
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">✗ Chyba</h4>
                        <p><?= $error ?></p>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p>Probíhá ověřování vašeho e-mailu...</p>
                    </div>
                <?php endif; ?>

                <a href="/dmp/public/index.php" class="btn btn-primary mt-3">Zpět na úvodní stránku</a>
            </div>
        </div>
    </div>
</div>

<?php include_once('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
