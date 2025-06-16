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
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_klanten && $result_klanten->num_rows > 0): ?>
                    <?php while($klant = $result_klanten->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $klant['KlantID']; ?></td>
                            <td>
                                <?php
                                    // Toon bedrijfsnaam of persoonlijke naam gebaseerd op type
                                    if ($klant['KlantType'] == 'Bedrijf') {
                                        echo htmlspecialchars($klant['Bedrijfsnaam']);
                                    } else {
                                        echo htmlspecialchars($klant['Voornaam'] . ' ' . $klant['Achternaam']);
                                    }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($klant['KlantType']); ?></td>
                            <td><?php echo htmlspecialchars($klant['Woonplaats']); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($klant['Emailadres']); ?>"><?php echo htmlspecialchars($klant['Emailadres']); ?></a></td>
                            <td class="actions">
                                <a href="klanten.php?id=<?php echo $klant['KlantID']; ?>" title="Bekijken"><i class="fa-solid fa-eye"></i></a>
                                <a href="klant_form.php?id=<?php echo $klant['KlantID']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Geen klanten gevonden.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'footer.php'; ?>
