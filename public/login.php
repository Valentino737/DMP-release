<?php
/**
 * Přihlašovací stránka
 * 
 * Zpracovává přihlášení uživatele pomocí e-mailu/uživ. jména a hesla.
 * Ověřuje heslo, kontroluje ban a ověření e-mailu.
 * Po úspěšném přihlášení nastavuje session a přesměruje na dashboard.
 */
session_start();
require_once(__DIR__ . '/../db/connection.php');
require_once(__DIR__ . '/../includes/csrf.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = "Neplatný požadavek. Zkuste to znovu.";
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';


    if (empty($login)) $errors[] = "E-mail nebo uživatelské jméno je povinné.";
    if (empty($password)) $errors[] = "Heslo je povinné.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, username, password, roleId, email_verified, is_banned FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Zkontroluje, zda je uživatel zablokován nebo zda nemá ověřený e-mail
            if ($user['is_banned']) {
                $errors[] = "Váš účet byl zablokován. Pro více informací kontaktujte podporu.";
            } elseif (!$user['email_verified']) {
                $errors[] = "Před přihlášením prosím ověřte svůj e-mail. Ověřovací odkaz najdete ve své doručené poště.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['roleId'] = $user['roleId'];

                header("Location: dashboard.php");
                exit;
            }
        } else {
            $errors[] = "Neplatné přihlašovací údaje.";
        }
    }
    }
}
?>

<?php include_once('../includes/header.php'); ?>

<head>
    <title>Přihlášení - PC Konfigurátor</title>
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
        .login-links .forgot-section {
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #DEE5E5;
        }
        .login-links a {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        .link-register {
            background: #007bff;
            color: white;
        }
        .link-register:hover {
            background: #0056b3;
            color: white;
        }
        .link-forgot {
            background: #ffc107;
            color: #333;
        }
        .link-forgot:hover {
            background: #e0a800;
            color: #333;
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
                    <h2 class="text-center">🔐 Přihlášení</h2>

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

                    <form method="POST" action="">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="login" class="form-label">E-mail nebo uživatelské jméno</label>
                            <input type="text" class="form-control" id="login" name="login" value="<?= htmlspecialchars($login ?? '') ?>" placeholder="e-mail nebo uživatelské jméno" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Heslo</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Vaše heslo" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-login">Přihlásit se</button>

                        <div class="login-links">
                            <div class="forgot-section text-center">
                                <p class="mb-2 text-muted small">Zapomněli jste heslo?</p>
                                <a href="forgot_password.php" class="link-forgot">🔑 Obnovit heslo</a>
                            </div>
                        
                            <div class="text-center mb-3">
                                <p class="mb-2 text-muted small">Nemáte účet?</p>
                                <a href="register.php" class="link-register">📝 Zaregistrovat se</a>
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
document.getElementById('togglePassword').addEventListener('click', function () {
    const pw = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>
