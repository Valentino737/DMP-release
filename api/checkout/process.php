<?php
/**
 * Zpracování platby a správa předplatného
 * 
 * Testovací platební brána – simuluje zpracování platby.
 * Podporuje:
 * - Výběr plánu (zobrazí platební formulář)
 * - Zpracování platby (aktualizuje předplatné v DB)
 * - Zrušení předplatného (downgrade na Free)
 * 
 * @method POST
 * @param int    tier        Úroveň předplatného (1–3)
 * @param string card_number Číslo karty (testovací)
 * @param string action      'cancel' pro zrušení předplatného
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Přesměrování, pokud uživatel není přihlášen
if (!isset($_SESSION['user_id'])) {
    header("Location: /dmp/public/login.php");
    exit;
}

$error = '';
$showPaymentForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Zpracování zrušení předplatného
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        if (!csrf_validate()) {
            $error = 'Neplatný CSRF token';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE users SET subscription = 1 WHERE id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                
                // Zalogování zrušení
                $logEntry = date('Y-m-d H:i:s') . " | User: {$_SESSION['user_id']} | Action: CANCEL_SUBSCRIPTION (downgraded to Free)\n";
                file_put_contents(__DIR__ . '/../../payment_log.txt', $logEntry, FILE_APPEND);
                
                // Přesměrování na stránku úspěchu
                $_SESSION['subscription_cancelled'] = true;
                header("Location: /dmp/public/upgrade.php?cancelled=1");
                exit;
            } catch (Exception $e) {
                error_log('Subscription cancel error: ' . $e->getMessage());
                $error = 'Chyba při zrušení. Zkuste to prosím znovu.';
            }
        }
    }
    // Pokud přichází z upgrade.php (výběr plánu)
    elseif (!isset($_POST['card_number'])) {
        if (!csrf_validate()) {
            $error = 'Neplatný CSRF token';
        } else {
            $tier = (int)($_POST['tier'] ?? 0);
            $tierName = $_POST['tier_name'] ?? '';
            
            if ($tier < 1 || $tier > 3) {
                $error = 'Neplatný plán';
            } else {
                // Zobrazení testovacího platebního formuláře
                $showPaymentForm = true;
            }
        }
    } else {
        // Zpracování testovacího platebního formuláře
        if (!csrf_validate()) {
            $error = 'Neplatný CSRF token';
        } else {
            $tier = (int)($_POST['tier'] ?? 0);
            $cardNumber = $_POST['card_number'] ?? '';
            
            // Testovací validace: pouze kontrola, že číslo karty není prázdné
            if (empty($cardNumber) || strlen($cardNumber) < 13) {
                $error = 'Neplatné číslo karty';
            } else {
                // Aktualizace předplatného v databázi
                try {
                    $stmt = $pdo->prepare('UPDATE users SET subscription = ? WHERE id = ?');
                    $stmt->execute([$tier, $_SESSION['user_id']]);
                    
                    // Zalogování testovací platby
                    $logEntry = date('Y-m-d H:i:s') . " | User: {$_SESSION['user_id']} | Tier: {$tier} | Payment processed\n";
                    file_put_contents(__DIR__ . '/../../payment_log.txt', $logEntry, FILE_APPEND);
                    
                    // Přesměrování na stránku úspěchu
                    $_SESSION['subscription_upgraded'] = true;
                    $_SESSION['upgraded_tier'] = $tier;
                    header("Location: /dmp/public/upgrade.php?success=1");
                    exit;
                } catch (Exception $e) {
                    error_log('Payment processing error: ' . $e->getMessage());
                    $error = 'Chyba při zpracování. Zkuste to prosím znovu.';
                }
            }
        }
    }
}

$successMessage = '';
if (isset($_GET['success']) && $_SESSION['subscription_upgraded'] ?? false) {
    $successMessage = 'Vaše předplatné bylo úspěšně aktivováno!';
    unset($_SESSION['subscription_upgraded']);
    unset($_SESSION['upgraded_tier']);
}

$tier = (int)($_POST['tier'] ?? $_GET['tier'] ?? 0);
$tierName = $_POST['tier_name'] ?? '';
$tierPrice = $_POST['price'] ?? '';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bezpečná platba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f9fafb; }
        .container { max-width: 600px; margin: 3rem auto; }
        .payment-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { font-weight: 600; color: #333; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        input:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .btn-pay { background: #28a745; color: white; padding: 12px; border: none; border-radius: 6px; width: 100%; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .btn-pay:hover { background: #218838; }
        .btn-back { background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; text-decoration: none; display: inline-block; }
        .error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #c3e6cb; }
        .order-summary { background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        .order-summary .row { display: flex; justify-content: space-between; }
        .fake-notice { background: #e2e3e5; border: 2px dashed #6c757d; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($successMessage): ?>
            <div class="payment-card">
                <div class="header">
                    <h2>✅ Úspěch!</h2>
                </div>
                <div class="success"><?= htmlspecialchars($successMessage) ?></div>
                <p style="text-align: center;">Vaše nové předplatné je nyní aktivní. Nyní můžete uložit více sestav!</p>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="/dmp/public/dashboard.php" class="btn-back">Zpět na dashboard</a>
                </div>
            </div>
        <?php elseif ($showPaymentForm): ?>
            <div class="payment-card">
                <div class="header">
                    <h2>💳 Fakturační údaje</h2>
                </div>
                
                <div class="order-summary">
                    <div class="row">
                        <span><strong>Plán:</strong> <?= htmlspecialchars($tierName) ?></span>
                        <span><strong>Cena:</strong> <?= htmlspecialchars($tierPrice) ?></span>
                    </div>
                </div>
                
                <div class="fake-notice">
                    ℹ️ <strong>Testovací režim:</strong> Toto je falešný platební formulář. Zadejte jakékoli údaje pro simulaci platby.
                </div>
                
                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tier" value="<?= $tier ?>">
                    <input type="hidden" name="tier_name" value="<?= htmlspecialchars($tierName) ?>">
                    <input type="hidden" name="price" value="<?= htmlspecialchars($tierPrice) ?>">
                    
                    <div class="form-group">
                        <label>Jméno majitele karty</label>
                        <input type="text" name="cardholder_name" placeholder="Jan Nováčík" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Číslo karty</label>
                        <input type="text" name="card_number" placeholder="4111 1111 1111 1111" maxlength="19" required>
                    </div>
                    
                    <div class="row" style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label>Platnost (MM/RR)</label>
                            <input type="text" name="expiry" placeholder="12/28" maxlength="5" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>CVC</label>
                            <input type="text" name="cvc" placeholder="123" maxlength="3" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required disabled>
                    </div>
                    
                    <button type="submit" class="btn-pay">Zaplatit <?= htmlspecialchars($tierPrice) ?></button>
                </form>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="/dmp/public/upgrade.php" class="btn-back">← Zpět</a>
                </div>
            </div>
        <?php else: ?>
            <div class="payment-card">
                <div class="header">
                    <h2 style="color: #e74c3c;">❌ Chyba</h2>
                </div>
                
                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php else: ?>
                    <div class="error">Neočekávaná chyba. Zkuste to znovu.</div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="/dmp/public/upgrade.php" class="btn-back">← Zpět na výběr plánů</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
