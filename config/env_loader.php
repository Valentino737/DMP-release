<?php
/**
 * Soubor pro načtení .env souboru
 * 
 * Načítá proměnné z .env souboru do PHP
 */

function loadEnvFile($envFile = null) {
    if ($envFile === null) {
        $envFile = __DIR__ . '/../.env';
    }
    
    if (!file_exists($envFile)) {
        return false;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Přeskočit komentáře
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsování KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Odstraní uvozovky pokud jsou použity
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }
            
            // Nastavit jako proměnnou prostředí
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
    
    return true;
}

// Načte .env soubor automaticky při připojení tohoto souboru
loadEnvFile();
?>
