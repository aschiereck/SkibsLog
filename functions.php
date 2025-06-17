<?php
/**
 * functions.php
 *
 * Bevat herbruikbare functies voor de SkibsLog applicatie.
 */

/**
 * Controleert of de ingelogde gebruiker de vereiste rol (of een hogere rol) heeft.
 * De hiërarchie is: admin > superuser > user > viewer.
 * Een admin heeft dus automatisch de rechten van een superuser, user en viewer.
 *
 * @param string $required_role De minimaal vereiste rol.
 * @return bool True als de gebruiker de rol heeft, anders false.
 */
function has_role(string $required_role): bool {
    // Haal de rol van de ingelogde gebruiker uit de sessie.
    $user_role = $_SESSION['user_rol'] ?? 'viewer';

    // Definieer de hiërarchie van de rollen.
    $roles_hierarchy = [
        'viewer' => 1,
        'user' => 2,
        'superuser' => 3,
        'admin' => 4
    ];

    // Controleer of de rol van de gebruiker gelijk is aan of hoger is dan de vereiste rol.
    return ($roles_hierarchy[$user_role] >= $roles_hierarchy[$required_role]);
}

?>
