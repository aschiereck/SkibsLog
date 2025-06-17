<?php
require 'header.php'; // Db-connectie en functies zijn nodig

// Controleer op geldige actie en ID
if (isset($_GET['actie']) && $_GET['actie'] == 'verwijder' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    // --- ROLBEVEILIGING ---
    if (!has_role('superuser')) {
        die('Ongeautoriseerde actie: U heeft geen rechten om een klant te verwijderen.');
    }

    $klantId = (int)$_GET['id'];

    // Beveiligde query met prepared statement
    $stmt = $db_connect->prepare("DELETE FROM Klanten WHERE KlantID = ?");
    $stmt->bind_param("i", $klantId);

    if ($stmt->execute()) {
        header("Location: klanten_overzicht.php");
        exit;
    } else {
        die("Er is een fout opgetreden bij het verwijderen van de klant.");
    }

} else {
    // Geen geldige actie, terug naar dashboard
    header("Location: index.php");
    exit;
}
?>
