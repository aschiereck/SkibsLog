<?php
require 'header.php'; // Db-connectie is nodig

// Controleer op geldige actie en ID
if (isset($_GET['actie']) && $_GET['actie'] == 'verwijder' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $klantId = (int)$_GET['id'];

    // Beveiligde query met prepared statement
    $stmt = $db_connect->prepare("DELETE FROM Klanten WHERE KlantID = ?");
    $stmt->bind_param("i", $klantId);

    if ($stmt->execute()) {
        // Succesvol, stuur terug naar overzicht
        header("Location: klanten.php");
        exit;
    } else {
        // Fout bij uitvoeren
        die("Er is een fout opgetreden bij het verwijderen van de klant.");
    }

} else {
    // Geen geldige actie, terug naar dashboard
    header("Location: index.php");
    exit;
}
?>
