<?php
$pageTitle = 'Zoekresultaten';
require 'header.php';

// Haal de zoekterm en scope op uit de URL
$zoekterm = '';
$scope = 'snel'; // Standaardwaarde

if (isset($_GET['q'])) {
    $zoekterm = trim($_GET['q']);
}
if (isset($_GET['scope']) && $_GET['scope'] == 'alles') {
    $scope = 'alles';
}

$results_jachten = [];
$results_klanten = [];

// Voer alleen een zoekopdracht uit als er een zoekterm is
if (!empty($zoekterm)) {
    $pageTitle = 'Resultaten voor "' . htmlspecialchars($zoekterm) . '"';
    $zoekterm_sql = '%' . $db_connect->real_escape_string($zoekterm) . '%';

    // --- Bouw de query voor Schepen ---
    $sql_jachten = "
        SELECT SchipID, Status, NaamSchip, MerkWerf, ModelType, Bouwjaar, Vraagprijs, Ligplaats 
        FROM Schepen 
        WHERE NaamSchip LIKE ? OR MerkWerf LIKE ? OR ModelType LIKE ?
    ";
    $params_jachten = ["sss", $zoekterm_sql, $zoekterm_sql, $zoekterm_sql];

    if ($scope == 'alles') {
        $sql_jachten .= " OR Ligplaats LIKE ? OR OmschrijvingAlg LIKE ?";
        $params_jachten[0] .= "ss";
        $params_jachten[] = $zoekterm_sql;
        $params_jachten[] = $zoekterm_sql;
        $pageTitle .= " (uitgebreid)";
    }
    $sql_jachten .= " ORDER BY SchipID DESC";
    
    $stmt_jachten = $db_connect->prepare($sql_jachten);
    $stmt_jachten->bind_param(...$params_jachten);
    $stmt_jachten->execute();
    $results_jachten = $stmt_jachten->get_result()->fetch_all(MYSQLI_ASSOC);

    // --- Bouw de query voor Klanten ---
    $sql_klanten = "
        SELECT KlantID, KlantType, Voornaam, Achternaam, Bedrijfsnaam, Woonplaats, Emailadres 
        FROM Klanten 
        WHERE Voornaam LIKE ? OR Achternaam LIKE ? OR Bedrijfsnaam LIKE ? OR Emailadres LIKE ?
    ";
    $params_klanten = ["ssss", $zoekterm_sql, $zoekterm_sql, $zoekterm_sql, $zoekterm_sql];

    if ($scope == 'alles') {
        $sql_klanten .= " OR Adres LIKE ? OR Woonplaats LIKE ? OR Notities LIKE ?";
        $params_klanten[0] .= "sss";
        $params_klanten[] = $zoekterm_sql;
        $params_klanten[] = $zoekterm_sql;
        $params_klanten[] = $zoekterm_sql;
    }
     $sql_klanten .= " ORDER BY KlantID DESC";

    $stmt_klanten = $db_connect->prepare($sql_klanten);
    $stmt_klanten->bind_param(...$params_klanten);
    $stmt_klanten->execute();
    $results_klanten = $stmt_klanten->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo $pageTitle; ?></h2>
        <?php if (!empty($zoekterm) && $scope == 'snel'): ?>
            <!-- Toon deze knop alleen als er een snelle zoekopdracht is uitgevoerd -->
            <form action="zoek.php" method="get">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($zoekterm); ?>">
                <input type="hidden" name="scope" value="alles">
                <button type="submit" class="action-button-header">
                    <i class="fa-solid fa-search-plus"></i> Zoek ook in alle overige velden
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Sectie voor gevonden jachten -->
    <div class="report-table-card">
        <h3><i class="fa-solid fa-ship"></i> Gevonden Jachten (<?php echo count($results_jachten); ?>)</h3>
        <?php if (!empty($results_jachten)): ?>
            <div class="table-container">
                <table>
                    <tbody>
                        <?php foreach($results_jachten as $jacht): ?>
                            <tr>
                                <td data-label="Naam"><?php echo htmlspecialchars($jacht['NaamSchip']); ?></td>
                                <td data-label="Merk & Model"><?php echo htmlspecialchars($jacht['MerkWerf'] . ' ' . $jacht['ModelType']); ?></td>
                                <td data-label="Status"><span class="status-<?php echo strtolower(str_replace(' ', '-', $jacht['Status'])); ?>"><?php echo htmlspecialchars($jacht['Status']); ?></span></td>
                                <td data-label="Acties" class="actions">
                                    <a href="jachten.php?id=<?php echo $jacht['SchipID']; ?>" title="Bekijken"><i class="fa-solid fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Geen jachten gevonden die voldoen aan uw zoekopdracht.</p>
        <?php endif; ?>
    </div>

    <!-- Sectie voor gevonden klanten -->
    <div class="report-table-card" style="margin-top: 2rem;">
        <h3><i class="fa-solid fa-users"></i> Gevonden Klanten (<?php echo count($results_klanten); ?>)</h3>
        <?php if (!empty($results_klanten)): ?>
            <div class="table-container">
                <table>
                    <tbody>
                        <?php foreach($results_klanten as $klant): ?>
                            <tr>
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
                                <td data-label="Acties" class="actions">
                                    <a href="klanten.php?id=<?php echo $klant['KlantID']; ?>" title="Bekijken"><i class="fa-solid fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Geen klanten gevonden die voldoen aan uw zoekopdracht.</p>
        <?php endif; ?>
    </div>

</section>

<?php require 'footer.php'; ?>
