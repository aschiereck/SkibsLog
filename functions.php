<?php
/**
 * functions.php
 *
 * Bevat herbruikbare functies voor de SkibsLog applicatie.
 */

/**
 * Controleert of de ingelogde gebruiker de vereiste rol (of een hogere rol) heeft.
 * De hiërarchie is: admin > superuser > user > viewer.
 */
function has_role(string $required_role): bool {
    $user_role = $_SESSION['user_rol'] ?? 'viewer';
    $roles_hierarchy = [
        'viewer' => 1,
        'user' => 2,
        'superuser' => 3,
        'admin' => 4
    ];
    return ($roles_hierarchy[$user_role] >= $roles_hierarchy[$required_role]);
}

/**
 * Logt een gebruikersactie naar de database.
 *
 * @param mysqli $db De databaseverbinding.
 * @param string $actie De omschrijving van de actie (bv. "Kostenpost gewijzigd").
 * @param string $details Optionele extra details (bv. "ID: 123 voor Jacht: 101").
 */
function log_activity(mysqli $db, string $actie, string $details = ''): void {
    $userId = $_SESSION['user_id'] ?? null;
    $gebruikersnaam = $_SESSION['user_naam'] ?? 'Onbekend';

    $stmt = $db->prepare("INSERT INTO ActivityLog (UserID, Gebruikersnaam, Actie, Details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $gebruikersnaam, $actie, $details);
    $stmt->execute();
}

?>
