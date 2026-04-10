<?php
/**
 * Hlavní konfigurační soubor aplikace
 * 
 * Obsahuje přístupové údaje k databázi.
 * Pro produkční nasazení změňte přihlašovací údaje.
 */
return [
    'db' => [
        'host' => 'localhost',
        'dbname' => "dmp",
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ]
];