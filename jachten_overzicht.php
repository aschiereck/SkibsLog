<?php
$pageTitle = 'Overzicht Motorjachten';
require 'header.php';

// Haal alle schepen op, de nieuwste eerst
$query_jachten = "SELECT SchipID, Status, NaamSchip, MerkWerf, ModelType, Bouwjaar, Vraagprijs, Ligplaats FROM Schepen ORDER BY SchipID DESC";
$result_jachten = $db_connect->query($query_jachten);
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
        <a href="jacht_form.php" class="action-button-header"><i class="fa-solid fa-plus"></i> Nieuw Jacht Toevoegen</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>Merk & Model</th>
                    <th>Bouwjaar</th>
                    <th>Status</th>
                    <th>Vraagprijs</th>
                    <th>Ligplaats</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_jachten && $result_jachten->num_rows > 0): ?>
                    <?php while($jacht = $result_jachten->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $jacht['SchipID']; ?></td>
                            <td><?php echo htmlspecialchars($jacht['NaamSchip']); ?></td>
                            <td><?php echo htmlspecialchars($jacht['MerkWerf'] . ' ' . $jacht['ModelType']); ?></td>
                            <td><?php echo $jacht['Bouwjaar']; ?></td>
                            <td><span class="status-<?php echo strtolower(str_replace(' ', '-', $jacht['Status'])); ?>"><?php echo htmlspecialchars($jacht['Status']); ?></span></td>
                            <td>€ <?php echo number_format($jacht['Vraagprijs'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($jacht['Ligplaats']); ?></td>
                            <td class="actions">
                                <a href="jachten.php?id=<?php echo $jacht['SchipID']; ?>" title="Bekijken"><i class="fa-solid fa-eye"></i></a>
                                <a href="jacht_form.php?id=<?php echo $jacht['SchipID']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">Geen motorjachten gevonden.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'footer.php'; ?>
