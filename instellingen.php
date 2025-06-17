<?php
$pageTitle = 'Instellingen';
require 'header.php';

// --- ROLBEVEILIGING ---
// Deze pagina is alleen toegankelijk voor admins.
if (!has_role('admin')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bekijken.</div></section>";
    require 'footer.php';
    exit;
}

// Haal alle gebruikers op om te tonen
$result_gebruikers = $db_connect->query("SELECT UserID, Gebruikersnaam, VolledigeNaam, Rol, IsActief FROM Gebruikers ORDER BY VolledigeNaam ASC");
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
    </div>

    <div class="report-table-card">
        <h3><i class="fa-solid fa-users-cog"></i> Gebruikersbeheer</h3>
        <p style="margin-bottom: 1.5rem;">Beheer hier de gebruikers en hun rechten binnen SkibsLog.</p>
        
        <a href="gebruiker_form.php" class="action-button-header" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-user-plus"></i> Nieuwe Gebruiker Toevoegen</a>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Gebruikersnaam</th>
                        <th>Rol</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_gebruikers && $result_gebruikers->num_rows > 0): ?>
                        <?php while($user = $result_gebruikers->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Naam"><?php echo htmlspecialchars($user['VolledigeNaam']); ?></td>
                                <td data-label="Gebruikersnaam"><?php echo htmlspecialchars($user['Gebruikersnaam']); ?></td>
                                <td data-label="Rol"><?php echo ucfirst($user['Rol']); ?></td>
                                <td data-label="Status"><?php echo ($user['IsActief'] ? 'Actief' : 'Inactief'); ?></td>
                                <td data-label="Acties" class="actions">
                                    <a href="gebruiker_form.php?id=<?php echo $user['UserID']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Geen gebruikers gevonden.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
