<?php
/**
 * Služba pro odesílání e-mailů
 * 
 * Využívá knihovnu PHPMailer pro SMTP odesílání e-mailů.
 * Podporuje ověřovací e-maily, resetování hesla a další.
 * V případě nedostupnosti SMTP přepne na souborový logger.
 */
require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/EmailLogger.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private ?PHPMailer $mail;
    private array $config;
    private EmailLogger $logger;
    private bool $useFileLogging = false;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/email_config.php';
        $this->logger = new EmailLogger();
        
        // Kontrola, zda použít souborové logování (užitečné pro ladění/vývoj)
        $this->useFileLogging = getenv('EMAIL_USE_FILE_LOGGING') === '1' || 
                                (
                                    empty($this->config['username']) || 
                                    empty($this->config['password'])
                                );
        
        if (!$this->useFileLogging) {
            try {
                $this->mail = new PHPMailer(true);
                
                // Konfigurace SMTP
                $this->mail->isSMTP();
                $this->mail->Host = $this->config['host'];
                $this->mail->Port = $this->config['port'];
                $this->mail->SMTPAuth = true;
                $this->mail->Username = $this->config['username'];
                $this->mail->Password = $this->config['password'];
                $this->mail->SMTPSecure = $this->config['encryption'];
                $this->mail->Timeout = $this->config['timeout'];
                
                // Ověření SSL certifikátu
                // V produkci: verify_peer = true (výchozí, bezpečné)
                // Ve vývoji (XAMPP): verify_peer = false (nutné řešení)
                $sslOptions = [
                    'ssl' => [
                        'verify_peer' => true,           // Výchozí: ověřovat SSL certifikáty
                        'verify_peer_name' => true,      // Ověřit, že hostname odpovídá certifikátu
                    ]
                ];
                
                // Výjimka pro vývoj: vypnutí SSL ověření pro XAMPP
                // Toto je nutné, protože XAMPP nemá správné CA certifikáty
                // DŮLEŽITÉ: V produkci toto odstraňte!
                if (getenv('APP_ENV') === 'development' || getenv('DISABLE_SSL_VERIFY') === '1') {
                    $sslOptions['ssl'] = [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ];
                }
                
                $this->mail->SMTPOptions = $sslOptions;
                
                // Ladící režim – vždy zapnutý pro vývoj
                $this->mail->SMTPDebug = ($this->config['debug'] || true) ? 2 : 0;
                $this->mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug [$level]: $str");
                };
                
                // Kódování
                $this->mail->CharSet = 'UTF-8';
            } catch (Exception $e) {
                error_log("PHPMailer initialization failed: " . $e->getMessage());
                $this->useFileLogging = true;
                $this->mail = null;
            }
        }
    }

    /**
     * Odeslání ověřovacího odkazu novému uživateli
     */
    public function sendVerificationEmail(string $email, string $username, string $verificationToken, string $verificationUrl): bool {
        try {
            $verificationLink = $verificationUrl . '?token=' . urlencode($verificationToken) . '&email=' . urlencode($email);
            
            $htmlBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                    .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
                    .button { display: inline-block; padding: 12px 24px; background: #0066cc; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                    .footer { font-size: 12px; color: #666; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Vítejte v PC Konfigurátoru!</h1>
                    </div>
                    <div class='content'>
                        <p>Ahoj <strong>" . htmlspecialchars($username) . "</strong>,</p>
                        <p>Děkujeme za registraci! Pro dokončení nastavení účtu prosím ověřte svou e-mailovou adresu.</p>
                        <p>Klikněte na tlačítko níže pro ověření e-mailu:</p>
                        <a href='" . htmlspecialchars($verificationLink) . "' class='button'>Ověřit e-mail</a>
                        <p>Nebo zkopírujte a vložte tento odkaz do prohlížeče:</p>
                        <p><small>" . htmlspecialchars($verificationLink) . "</small></p>
                        <p>Platnost odkazu vyprší za 24 hodin.</p>
                        <p>Pokud jste si tento účet nevytvořili, tento e-mail ignorujte.</p>
                        <div class='footer'>
                            <p>&copy; 2026 DMP Valentýn Vimmer. Všechna práva vyhrazena.</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>";
            
            if ($this->useFileLogging) {
                return $this->logger->logEmail($email, $username, 'Ověřte svůj e-mail - PC Konfigurátor', $htmlBody, 'verification');
            }
            
            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mail->addAddress($email, $username);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Ověřte svůj e-mail - PC Konfigurátor';
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = "Vítejte! Ověřte svůj e-mail na: " . $verificationLink;
            
            if ($this->mail->send()) {
                return true;
            } else {
                error_log("Verification email send failed: " . $this->mail->ErrorInfo);
                return false;
            }
        } catch (Exception $e) {
            error_log("Verification email exception: " . $e->getMessage());
            if ($this->mail) {
                error_log("SMTP Error Info: " . $this->mail->ErrorInfo);
            }
            return false;
        }
    }

    /**
     * Odeslání odkazu pro obnovu hesla
     */
    public function sendPasswordResetEmail(string $email, string $username, string $resetToken, string $resetUrl): bool {
        try {
            $resetLink = $resetUrl . '?token=' . urlencode($resetToken) . '&email=' . urlencode($email);
            
            $htmlBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                    .content { background: #f9fafb; padding: 20px; border-radius: 0 0 8px 8px; }
                    .button { display: inline-block; padding: 12px 24px; background: #0066cc; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                    .footer { font-size: 12px; color: #666; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Obnova hesla</h1>
                    </div>
                    <div class='content'>
                        <p>Ahoj <strong>" . htmlspecialchars($username) . "</strong>,</p>
                        <p>Obdrželi jsme žádost o obnovu vašeho hesla. Klikněte na tlačítko níže pro nastavení nového hesla:</p>
                        <a href='" . htmlspecialchars($resetLink) . "' class='button'>Obnovit heslo</a>
                        <p>Nebo zkopírujte a vložte tento odkaz do prohlížeče:</p>
                        <p><small>" . htmlspecialchars($resetLink) . "</small></p>
                        <p>Platnost odkazu vyprší za 1 hodinu.</p>
                        <p>Pokud jste o obnovu hesla nežádali, tento e-mail ignorujte.</p>
                        <div class='footer'>
                            <p>&copy; 2026 DMP Valentýn Vimmer. Všechna práva vyhrazena.</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>";
            
            if ($this->useFileLogging) {
                return $this->logger->logEmail($email, $username, 'Obnova hesla - PC Konfigurátor', $htmlBody, 'reset');
            }
            
            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mail->addAddress($email, $username);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Obnova hesla - PC Konfigurátor';
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = "Obnovte své heslo na: " . $resetLink;
            
            if ($this->mail->send()) {
                return true;
            } else {
                error_log("Password reset email send failed: " . $this->mail->ErrorInfo);
                return false;
            }
        } catch (Exception $e) {
            error_log("Password reset email exception: " . $e->getMessage());
            if ($this->mail) {
                error_log("SMTP Error Info: " . $this->mail->ErrorInfo);
            }
            return false;
        }
    }

    /**
     * Vyčištění příjemců pro další e-mail
     */
    public function clearRecipients(): void {
        if ($this->mail) {
            $this->mail->clearAllRecipients();
        }
    }
}
?>
?>
