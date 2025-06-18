<?php
$pageTitle = 'Activiteitenlogboek';
require 'header.php';

// --- ROLBEVEILIGING ---
// Deze pagina is alleen toegankelijk voor superusers en admins.
if (!has_role('superuser')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bekijken.</div></section>";
    require 'footer.php';
    exit;
}

// Haal alle logs op, de nieuwste eerst
$result_logs = $db_connect->query("SELECT LogID, UserID, Gebruikersnaam, Actie, Details, Timestamp FROM ActivityLog ORDER BY Timestamp DESC");
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
    </div>

    <div class="report-table-card">
        <h3><i class="fa-solid fa-clipboard-list"></i> Recente Activiteiten</h3>
        <p style="margin-bottom: 1.5rem;">Een overzicht van alle belangrijke acties die in het systeem zijn uitgevoerd.</p>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tijdstip</th>
                        <th>Gebruiker</th>
                        <th>Actie</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_logs && $result_logs->num_rows > 0): ?>
                        <?php while($log = $result_logs->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Tijdstip"><?php echo date('d-m-Y H:i:s', strtotime($log['Timestamp'])); ?></td>
                                <td data-label="Gebruiker"><?php echo htmlspecialchars($log['Gebruikersnaam']); ?> (ID: <?php echo $log['UserID']; ?>)</td>
                                <td data-label="Actie"><?php echo htmlspecialchars($log['Actie']); ?></td>
                                <td data-label="Details"><?php echo htmlspecialchars($log['Details']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Geen activiteiten gevonden in het logboek.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
