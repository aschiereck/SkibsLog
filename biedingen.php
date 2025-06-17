<?php
$pageTitle = 'Overzicht Biedingen';
require 'header.php';

// Haal alle biedingen op, inclusief jacht- en klantgegevens
$query_biedingen = "
    SELECT 
        bl.BodID,
        bl.DatumTijdBod,
        bl.BodBedrag,
        bl.Status,
        s.SchipID,
        s.NaamSchip,
        k.KlantID,
        k.Voornaam,
        k.Achternaam,
        k.Bedrijfsnaam,
        k.KlantType
    FROM BiedingenLog bl
    JOIN Schepen s ON bl.SchipID = s.SchipID
    JOIN Klanten k ON bl.KlantID = k.KlantID
    ORDER BY bl.DatumTijdBod DESC
";
$result_biedingen = $db_connect->query($query_biedingen);
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
        <a href="bod_form.php" class="action-button-header"><i class="fa-solid fa-plus"></i> Nieuw Bod Toevoegen</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Jacht</th>
                    <th>Bieder</th>
                    <th>Bedrag</th>
                    <th>Status</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_biedingen && $result_biedingen->num_rows > 0): ?>
                    <?php while($bod = $result_biedingen->fetch_assoc()): ?>
                        <?php
                            $klantNaam = ($bod['KlantType'] == 'Bedrijf') 
                                ? $bod['Bedrijfsnaam'] 
                                : $bod['Voornaam'] . ' ' . $bod['Achternaam'];
                        ?>
                        <tr>
                            <td data-label="Datum"><?php echo date('d M Y H:i', strtotime($bod['DatumTijdBod'])); ?></td>
                            <td data-label="Jacht"><a href="jachten.php?id=<?php echo $bod['SchipID']; ?>"><?php echo htmlspecialchars($bod['NaamSchip']); ?></a></td>
                            <td data-label="Bieder"><a href="klanten.php?id=<?php echo $bod['KlantID']; ?>"><?php echo htmlspecialchars($klantNaam); ?></a></td>
                            <td data-label="Bedrag">€ <?php echo number_format($bod['BodBedrag'], 0, ',', '.'); ?></td>
                            <td data-label="Status"><span class="status-<?php echo strtolower(str_replace(' ', '-', $bod['Status'])); ?>"><?php echo htmlspecialchars($bod['Status']); ?></span></td>
                            <td data-label="Acties" class="actions">
                                <a href="bod_form.php?id=<?php echo $bod['BodID']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Geen biedingen gevonden.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'footer.php'; ?>
