<?php
/*
 * config.php
 * Configuratiebestand voor de SkibsLog applicatie.
 * Gebaseerd op het nieuwe, robuustere template.
 */

// --- Globale Instellingen ---

// Zet de standaard tijdzone voor de hele applicatie.
// Dit voorkomt problemen met datums en tijden.
date_default_timezone_set('Europe/Amsterdam');

// Start de PHP-sessie, nodig voor o.a. login-functionaliteit.
session_start();


// --- Foutrapportage ---
// Zet op 1 voor ontwikkeling, zet op 0 voor een live productieomgeving.
ini_set('display_errors', 1);
error_reporting(E_ALL);


// --- Database Constanten ---
define('DB_HOST', 'localhost');
define('DB_USER', 'SKL01');
define('DB_PASS', '158Ltb_9d');
define('DB_NAME', 'SkibsLog');


// --- Applicatie Constanten ---
/**
 * De volledige basis-URL van uw applicatie.
 * Handig voor het maken van absolute links. Let op de slash aan het einde!
 */
define('BASE_URL', 'https://draads.nl/SkibsLog/');


// --- Databaseverbinding Opzetten (Object-georiënteerde stijl) ---
$db_connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Controleer de verbinding op een veilige manier
if ($db_connect->connect_error) {
    // Log de technische fout voor de beheerder (wordt niet getoond aan de gebruiker).
    error_log("Databaseverbinding mislukt: " . $db_connect->connect_error);
    
    // Toon een generieke, gebruiksvriendelijke foutmelding en stop het script.
    die("Er is een technisch probleem met de databaseverbinding. Probeer het later opnieuw.");
}

/*
 * Stel de karakterset in op utf8mb4.
 * Dit zorgt ervoor dat speciale tekens correct worden opgeslagen en weergegeven.
 */
$db_connect->set_charset("utf8mb4");

?>
