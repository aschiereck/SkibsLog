<?php
$pageTitle = 'Overzicht Klanten';
require 'header.php';

// Haal alle klanten op
$query_klanten = "SELECT KlantID, KlantType, Voornaam, Achternaam, Bedrijfsnaam, Woonplaats, Emailadres FROM Klanten ORDER BY KlantID DESC";
$result_klanten = $db_connect->query($query_klanten);
?>

<section class="content-page">
     <div class="page-header">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
        <a href="klant_form.php" class="action-button-header"><i class="fa-solid fa-plus"></i> Nieuwe Klant Toevoegen</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>Type</th>
                    <th>Woonplaats</th>
                    <th>Emailadres</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_klanten && $result_klanten->num_rows > 0): ?>
                    <?php while($klant = $result_klanten->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID">
                                <a href="klanten.php?id=<?php echo $klant['KlantID']; ?>" class="row-link"></a>
                                <?php echo $klant['KlantID']; ?>
                            </td>
                            <td data-label="Naam">
                                <?php
                                    if ($klant['KlantType'] == 'Bedrijf') {
                                        echo htmlspecialchars($klant['Bedrijfsnaam']);
                                    } else {
                                        echo htmlspecialchars($klant['Voornaam'] . ' ' . $klant['Achternaam']);
                                    }
                                ?>
                            </td>
                            <td data-label="Type"><?php echo htmlspecialchars($klant['KlantType']); ?></td>
                            <td data-label="Woonplaats"><?php echo htmlspecialchars($klant['Woonplaats']); ?></td>
                            <td data-label="Emailadres"><?php echo htmlspecialchars($klant['Emailadres']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Geen klanten gevonden.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'footer.php'; ?>
