<?php
/*
 * config.php
 * Configuratiebestand voor de SkibsLog applicatie.
 * Bevat database-credentials en andere globale instellingen.
 */

// --- DATABASE INSTELLINGEN ---
// LET OP: Voor de veiligheid is het aan te raden dit bestand buiten de publieke webmap te plaatsen.

/**
 * De hostnaam van uw database server. Vaak is dit 'localhost' als uw website en database
 * op dezelfde server draaien, ook al is de domeinnaam draads.nl.
 * Probeer 'localhost' als 'draads.nl' niet werkt.
 */
define('DB_SERVER', 'draads.nl');

/**
 * De gebruikersnaam voor de database.
 */
define('DB_USERNAME', 'SLK01');

/**
 * Het wachtwoord voor de database.
 */
define('DB_PASSWORD', '158Ltb_9d');

/**
 * De naam van de database.
 */
define('DB_NAME', 'SkibsLog');


// --- APPLICATIE INSTELLINGEN ---

/**
 * De volledige basis-URL van uw applicatie.
 * Dit is handig voor het maken van absolute links in uw code.
 * Let op de slash aan het einde!
 */
define('BASE_URL', 'https://draads.nl/SkibsLog/');


// --- DATABASE VERBINDING OPZETTEN ---

/* Probeer verbinding te maken met de MySQL database */
$db_connect = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Controleer de verbinding
if($db_connect === false){
    // Stop de uitvoering en toon een foutmelding als de verbinding mislukt.
    // In een live-omgeving zou u dit wellicht willen loggen i.p.v. tonen aan de gebruiker.
    die("FOUT: Kon geen verbinding maken met de database. " . mysqli_connect_error());
}

/*
 * Stel de karakterset in op utf8mb4.
 * Dit zorgt ervoor dat speciale tekens (zoals accenten of emoji's) correct worden opgeslagen en weergegeven.
 */
mysqli_set_charset($db_connect, "utf8mb4");

?>
