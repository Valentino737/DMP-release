<?php
/**
 * Jednoduchý souborový logger e-mailů pro vývoj
 * 
 * Místo odesílání e-mailů přes SMTP je zapisuje do lokálního souboru.
 * Užitečné pro testování, když SMTP nefunguje.
 */

class EmailLogger {
    private string $logDir;

    public function __construct() {
        $this->logDir = __DIR__ . '/../logs';
        
        // Vytvoření složky logs, pokud neexistuje
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Zalogování e-mailu do souboru místo odeslání
     */
    public function logEmail(string $email, string $username, string $subject, string $body, string $type): bool {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $logFile = $this->logDir . '/emails_' . date('Y-m-d') . '.log';
            
            $logEntry = "=== EMAIL LOG ===\n";
            $logEntry .= "Type: {$type}\n";
            $logEntry .= "Timestamp: {$timestamp}\n";
            $logEntry .= "To: {$email} ({$username})\n";
            $logEntry .= "Subject: {$subject}\n";
            $logEntry .= "---\n";
            $logEntry .= $body . "\n";
            $logEntry .= "==================\n\n";
            
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            
            // Také vytvořit klikatelný HTML soubor s odkazem pro ověřovací/reset e-maily
            if ($type === 'verification' || $type === 'reset') {
                $this->createClickableLink($type, $body);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Email logging error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extrakce a vytvoření souboru s klikatelným odkazem
     */
    private function createClickableLink(string $type, string $body): void {
        if (preg_match("/<a href='([^']+)'/", $body, $matches)) {
            // Dekódování HTML entit z odkazu (převod &amp; zpět na &)
            $link = html_entity_decode($matches[1]);
            $linkFile = $this->logDir . '/' . $type . '_link_' . time() . '.html';
            
            $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test Email Link</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; background: #f9fafb; }
                    .card { background: white; padding: 20px; border-radius: 8px; max-width: 600px; }
                    a { display: inline-block; padding: 12px 24px; background: #0066cc; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <h2>Test Email Link</h2>
                    <p>Email was logged to file. Click the link below to test:</p>
                    <a href='" . htmlspecialchars($link) . "'>Click Here to Verify/Reset</a>
                    <p><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>
                </div>
            </body>
            </html>";
            
            file_put_contents($linkFile, $html);
        }
    }

    /**
     * Získání cesty k dnešnímu log souboru
     */
    public function getTodayLogFile(): string {
        return $this->logDir . '/emails_' . date('Y-m-d') . '.log';
    }
}
?>
