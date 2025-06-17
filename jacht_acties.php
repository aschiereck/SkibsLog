<?php
require 'header.php'; // We hebben de db-connectie en functies nodig

// Controleer of de actie en ID correct zijn opgegeven
if (isset($_GET['actie']) && $_GET['actie'] == 'verwijder' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    // --- ROLBEVEILIGING ---
    // Alleen een superuser of admin mag data verwijderen.
    if (!has_role('superuser')) {
        die('Ongeautoriseerde actie: U heeft geen rechten om een jacht te verwijderen.');
    }

    $jachtId = (int)$_GET['id'];

    // --- Gebruik een prepared statement om SQL-injectie te voorkomen ---
    $stmt = $db_connect->prepare("DELETE FROM Schepen WHERE SchipID = ?");
    $stmt->bind_param("i", $jachtId);

    // Voer de query uit
    if ($stmt->execute()) {
        header("Location: jachten_overzicht.php");
        exit;
    } else {
        die("Er is een fout opgetreden bij het verwijderen van het jacht.");
    }

} else {
    // Geen geldige actie, stuur terug naar het dashboard
    header("Location: index.php");
    exit;
}
?>
