<?php
/**
 * Registrační stránka
 * 
 * Zpracovává registraci nového uživatele.
 * Validuje vstupy, hashuje heslo, generuje ověřovací token
 * a odesílá ověřovací e-mail přes EmailService.
 */
session_start();
require_once(__DIR__ . '/../db/connection.php');
require_once(__DIR__ . '/../includes/csrf.php');
require_once(__DIR__ . '/../includes/EmailService.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = "Neplatný požadavek. Zkuste to znovu.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $roleId = 1;

        if (empty($username)) $errors[] = "Uživatelské jméno je povinné.";
        if (empty($email)) $errors[] = "E-mail je povinný.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Neplatný formát e-mailu.";
        if (empty($password)) $errors[] = "Heslo je povinné.";
        if (strlen($password) < 8) $errors[] = "Heslo musí mít alespoň 8 znaků.";
        if ($password !== $confirm_password) $errors[] = "Hesla se neshodují.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "E-mail nebo uživatelské jméno je již registrováno.";
            }
        }

        if (empty($errors)) {
            try {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $verificationToken = bin2hex(random_bytes(32));
                $verificationExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, email_verified, password, roleId, verification_token, verification_token_expires) 
                    VALUES (?, ?, 0, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$username, $email, $hashed, $roleId, $verificationToken, $verificationExpires])) {
                    // Send verification email
                    try {
                        $emailService = new EmailService();
                        $verificationUrl = "http://" . $_SERVER['HTTP_HOST'] . "/dmp/public/verify_email.php";
                        
                        if ($emailService->sendVerificationEmail($email, $username, $verificationToken, $verificationUrl)) {
                            $success = "Registrace úspěšná! Zkontrolujte svůj e-mail a ověřte účet. Ověřovací odkaz vyprší za 24 hodin.";
                        } else {
                            $errors[] = "Účet byl vytvořen, ale ověřovací e-mail se nepodařilo odeslat. Kontaktujte podporu.";
                        }
                    } catch (Exception $e) {
                        error_log("Email send error: " . $e->getMessage());
                        $errors[] = "Účet byl vytvořen, ale odeslání ověřovacího e-mailu selhalo. Kontaktujte podporu.";
                    }
                } else {
                    $errors[] = "Chyba databáze. Zkuste to znovu.";
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $errors[] = "Při registraci došlo k chybě.";
            }
        }
    }
}
?>

<?php include_once('../includes/header.php'); ?>

<head>
    <title>Registrace - PC Konfigurátor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        .login-container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .login-form h2 {
            font-weight: 700;
            color: #0A0908;
            margin-bottom: 24px;
        }
        .btn-login {
            background: #618B4A;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            margin-top: 8px;
        }
        .btn-login:hover {
            background: #4a6b38;
            color: white;
        }
        .login-links {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #DEE5E5;
        }
        .login-links .link-row {
            margin-bottom: 12px;
        }
        .login-links a {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        .link-login {
            background: #007bff;
            color: white;
        }
        .link-login:hover {
            background: #0056b3;
            color: white;
        }
        .link-home {
            color: #618B4A;
            border: 2px solid #618B4A;
        }
        .link-home:hover {
            background: #618B4A;
            color: white;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="login-container">
                <div class="login-form">
                    <h2 class="text-center">📝 Vytvořit účet</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Uživatelské jméno</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Emailová adresa</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Heslo</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Potvrzení hesla</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-login">Zaregistrovat se</button>

                        <div class="login-links">
                            <div class="text-center mb-3">
                                <p class="mb-2 text-muted small">Už máte účet?</p>
                                <a href="login.php" class="link-login">🔐 Přihlásit se</a>
                            </div>
                            
                            <div class="text-center">
                                <a href="index.php" class="link-home">← Zpět na domovskou stránku</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('#togglePassword, #toggleConfirmPassword').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const input = this.closest('.input-group').querySelector('input');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});
</script>
