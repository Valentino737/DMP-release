<?php
/**
 * Stránka pro zapomenuté heslo
 * 
 * Uživatel zadá e-mail, systém vygeneruje reset token
 * a odešle odkaz pro obnovu hesla přes EmailService.
 * Token vyprší za 1 hodinu.
 */
session_start();
require_once(__DIR__ . '/../db/connection.php');
require_once(__DIR__ . '/../includes/csrf.php');
require_once(__DIR__ . '/../includes/EmailService.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        } else {
            try {
                // Najde uživatele podle jeho emailu
                $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Vygeneruje reset token
                    $resetToken = bin2hex(random_bytes(32));
                    $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Uloží token do databáze
                    $stmt = $pdo->prepare('
                        UPDATE users 
                        SET password_reset_token = ?, password_reset_token_expires = ? 
                        WHERE id = ?
                    ');
                    
                    if ($stmt->execute([$resetToken, $resetExpires, $user['id']])) {
                        // Odeslat email s resetem hesla
                        try {
                            $emailService = new EmailService();
                            $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/dmp/public/reset_password.php";
                            
                            if ($emailService->sendPasswordResetEmail($email, $user['username'], $resetToken, $resetUrl)) {
                                $success = "Instrukce pro reset hesla byly odeslány na váš email. Zkontrolujte svou schránku pro odkaz (platnost 1 hodina).";
                            } else {
                                $errors[] = "Nepodařilo se odeslat email pro reset hesla. Zkuste to prosím později.";
                            }
                        } catch (Exception $e) {
                            error_log("Chyba při odesílání emailu pro reset hesla: " . $e->getMessage());
                            $errors[] = "Nepodařilo se odeslat email pro reset hesla. Zkuste to prosím později.";
                        }
                    } else {
                        $errors[] = "Chyba databáze. Zkuste to prosím znovu.";
                    }
                } else {
                    // Neodhaluje, zda email existuje nebo ne (bezpečnostní doporučení)
                    $success = "Pokud účet s tímto emailem existuje, obdržíte instrukce pro reset hesla.";
                }
            } catch (Exception $e) {
                error_log("Chyba při zapomenutém hesle: " . $e->getMessage());
                $errors[] = "Došlo k chybě. Zkuste to prosím znovu.";
            }
        }
    }
}
?>

<?php include_once('../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Resetovat heslo - PC Konfigurátor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4 text-center">Resetovat heslo</h2>

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

            <?php if (empty($success)): ?>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Emailová adresa</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    <small class="text-muted">Zadejte emailovou adresu spojenou s vaším účtem.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100">Odeslat odkaz pro reset</button>

                <p class="mt-3 text-center">
                    Pamatujete si své heslo? <a href="login.php">Přihlaste se zde</a>
                </p>

                <p class="text-center"><a href="index.php">Zpět na hlavní stránku</a></p>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
