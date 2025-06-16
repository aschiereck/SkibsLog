<?php
require 'header.php'; // We hebben de db-connectie nodig

// Controleer of de actie en ID correct zijn opgegeven
if (isset($_GET['actie']) && $_GET['actie'] == 'verwijder' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $jachtId = (int)$_GET['id'];

    // --- Gebruik een prepared statement om SQL-injectie te voorkomen ---
    $stmt = $db_connect->prepare("DELETE FROM Schepen WHERE SchipID = ?");
    $stmt->bind_param("i", $jachtId);

    // Voer de query uit
    if ($stmt->execute()) {
        // Succesvol verwijderd, stuur gebruiker terug naar het overzicht
        // TODO: Voeg een succesmelding toe (Fase 4)
        header("Location: jachten.php");
        exit;
    } else {
        // Fout bij verwijderen
        // TODO: Voeg een foutmelding toe (Fase 4)
        die("Er is een fout opgetreden bij het verwijderen van het jacht.");
    }

} else {
    // Geen geldige actie, stuur terug naar het dashboard
    header("Location: index.php");
    exit;
}
?>
