<?php
$pageTitle = 'Agenda Overzicht';
require 'header.php';

// Haal alle geplande bezichtigingen op vanaf vandaag
$vandaag = date('Y-m-d');
$query_agenda = "
    SELECT 
        b.BezichtigingID, -- ID TOEGEVOEGD
        b.Datum, 
        b.Tijd, 
        b.Status, 
        b.Begeleider,
        s.SchipID,
        s.NaamSchip,
        k.KlantID,
        k.Voornaam,
        k.Achternaam,
        k.Bedrijfsnaam,
        k.KlantType
    FROM Bezichtigingen b
    JOIN Schepen s ON b.SchipID = s.SchipID
    JOIN Klanten k ON b.KlantID = k.KlantID
    WHERE b.Datum >= '{$vandaag}'
    ORDER BY b.Datum, b.Tijd ASC
";
$result_agenda = $db_connect->query($query_agenda);
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
        <a href="bezichtiging_form.php" class="action-button-header"><i class="fa-solid fa-plus"></i> Nieuwe Bezichtiging Plannen</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Datum & Tijd</th>
                    <th>Jacht</th>
                    <th>Klant</th>
                    <th>Begeleider</th>
                    <th>Status</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_agenda && $result_agenda->num_rows > 0): ?>
                    <?php while($item = $result_agenda->fetch_assoc()): ?>
                        <?php
                            $klantNaam = ($item['KlantType'] == 'Bedrijf') 
                                ? $item['Bedrijfsnaam'] 
                                : $item['Voornaam'] . ' ' . $item['Achternaam'];
                        ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($item['Datum'])); ?> om <?php echo date('H:i', strtotime($item['Tijd'])); ?></td>
                            <td><a href="jachten.php?id=<?php echo $item['SchipID']; ?>"><?php echo htmlspecialchars($item['NaamSchip']); ?></a></td>
                            <td><a href="klanten.php?id=<?php echo $item['KlantID']; ?>"><?php echo htmlspecialchars($klantNaam); ?></a></td>
                            <td><?php echo htmlspecialchars($item['Begeleider']); ?></td>
                            <td><span class="status-<?php echo strtolower(str_replace(' ', '-', $item['Status'])); ?>"><?php echo htmlspecialchars($item['Status']); ?></span></td>
                            <td class="actions">
                                <!-- Link is nu correct -->
                                <a href="bezichtiging_form.php?id=<?php echo $item['BezichtigingID']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                                <a href="#" title="Markeer als afgerond"><i class="fa-solid fa-check"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Geen geplande bezichtigingen gevonden.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'footer.php'; ?>
