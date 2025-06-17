<?php
$pageTitle = 'Jacht Details';
require 'header.php';

// Controleer of er een ID is meegegeven in de URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Ongeldig Jacht ID</h1><p>Er is geen geldig ID voor een jacht opgegeven.</p>";
    require 'footer.php';
    exit;
}

$jachtId = (int)$_GET['id'];

// --- Haal de hoofdgegevens van het jacht op ---
$stmt_jacht = $db_connect->prepare("SELECT * FROM Schepen WHERE SchipID = ?");
$stmt_jacht->bind_param("i", $jachtId);
$stmt_jacht->execute();
$result_jacht = $stmt_jacht->get_result();

if ($result_jacht->num_rows === 0) {
    echo "<h1>Jacht niet gevonden</h1><p>Er kon geen jacht worden gevonden met ID: " . htmlspecialchars($jachtId) . "</p>";
    require 'footer.php';
    exit;
}
$jacht = $result_jacht->fetch_assoc();
$pageTitle = htmlspecialchars($jacht['NaamSchip']);

// --- Haal ALLE gerelateerde klanten en relaties op, INCLUSIEF BIEDINGEN ---
$stmt_relaties = $db_connect->prepare("
    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Huidige Eigenaar' as RelatieType, ksr.Startdatum as RelatieDatum, NULL as BodBedrag, NULL as BodStatus
    FROM KlantSchipRelaties ksr
    JOIN Klanten k ON ksr.KlantID = k.KlantID
    WHERE ksr.SchipID = ? AND ksr.RelatieType = 'Huidige Eigenaar'

    UNION

    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Ex-Eigenaar' as RelatieType, ksr.Einddatum as RelatieDatum, NULL, NULL
    FROM KlantSchipRelaties ksr
    JOIN Klanten k ON ksr.KlantID = k.KlantID
    WHERE ksr.SchipID = ? AND ksr.RelatieType = 'Ex-Eigenaar'

    UNION

    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Geïnteresseerd' as RelatieType, b.Datum as RelatieDatum, NULL, NULL
    FROM Bezichtigingen b
    JOIN Klanten k ON b.KlantID = k.KlantID
    WHERE b.SchipID = ?

    UNION

    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Bod' as RelatieType, bl.DatumTijdBod as RelatieDatum, bl.BodBedrag, bl.Status as BodStatus
    FROM BiedingenLog bl
    JOIN Klanten k ON bl.KlantID = k.KlantID
    WHERE bl.SchipID = ?

    ORDER BY FIELD(RelatieType, 'Huidige Eigenaar', 'Bod', 'Geïnteresseerd', 'Ex-Eigenaar'), RelatieDatum DESC
");
$stmt_relaties->bind_param("iiii", $jachtId, $jachtId, $jachtId, $jachtId);
$stmt_relaties->execute();
$result_relaties = $stmt_relaties->get_result();
$relaties = $result_relaties->fetch_all(MYSQLI_ASSOC);

?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($jacht['MerkWerf'] . ' - ' . $jacht['ModelType']); ?></h2>
        <?php if (has_role('user')): ?>
            <div>
                <a href="jacht_form.php?id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-pencil"></i> Wijzigen</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="interactive-container">
        <div class="main-card-container">
            <div class="main-card">
                <h3><?php echo htmlspecialchars($jacht['NaamSchip']); ?></h3>
                <p class="main-card-subtitle"><?php echo $jacht['Bouwjaar']; ?> - <?php echo htmlspecialchars($jacht['Ligplaats']); ?></p>
                <div class="main-card-price">€ <?php echo number_format($jacht['Vraagprijs'], 0, ',', '.'); ?></div>
                <div class="main-card-specs">
                    <span><i class="fa-solid fa-ruler-vertical"></i> <?php echo $jacht['Lengte']; ?>m</span>
                    <span><i class="fa-solid fa-ruler-horizontal"></i> <?php echo $jacht['Breedte']; ?>m</span>
                    <span><i class="fa-solid fa-anchor"></i> <?php echo $jacht['Diepgang']; ?>m</span>
                </div>
                 <div class="main-card-detaillist">
                    <ul>
                        <li><strong>Status:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $jacht['Status'])); ?>"><?php echo htmlspecialchars($jacht['Status']); ?></span></li>
                        <li><strong>Materiaal:</strong> <?php echo htmlspecialchars($jacht['MateriaalRomp']); ?></li>
                        <li><strong>BTW Status:</strong> <?php echo htmlspecialchars($jacht['BTWStatus']); ?></li>
                    </ul>
                </div>
                <h4>Omschrijving</h4>
                <p class="main-card-description"><?php echo nl2br(htmlspecialchars($jacht['OmschrijvingAlg'])); ?></p>
            </div>
        </div>

        <div class="tabs-container">
            <ul class="tab-list">
                <?php foreach ($relaties as $index => $relatie): 
                     $relatieNaam = ($relatie['KlantType'] == 'Bedrijf') ? $relatie['Bedrijfsnaam'] : $relatie['Voornaam'] . ' ' . $relatie['Achternaam'];
                     $relatieClass = 'rel-' . strtolower(str_replace(' ', '-', $relatie['RelatieType']));
                ?>
                    <li class="tab-item <?php echo $relatieClass; ?> <?php echo ($index == 0) ? 'active' : ''; ?>" data-tab="tab-<?php echo $relatie['KlantID'].'-'.$index; ?>">
                        <?php echo htmlspecialchars($relatieNaam); ?>
                    </li>
                <?php endforeach; ?>
                <?php if (has_role('user')): ?>
                    <li class="tab-item add-new-tab" data-tab="tab-add-new"><i class="fa-solid fa-plus"></i></li>
                <?php endif; ?>
            </ul>
            <div class="tab-content">
                <?php foreach ($relaties as $index => $relatie):
                    $relatieNaam = ($relatie['KlantType'] == 'Bedrijf') ? $relatie['Bedrijfsnaam'] : $relatie['Voornaam'] . ' ' . $relatie['Achternaam'];
                    $relatieClass = 'rel-' . strtolower(str_replace(' ', '-', $relatie['RelatieType']));
                ?>
                    <div class="relation-card <?php echo $relatieClass; ?> <?php echo ($index == 0) ? 'active' : ''; ?>" id="tab-<?php echo $relatie['KlantID'].'-'.$index; ?>">
                        <div class="relation-card-header">
                            <h4><?php echo htmlspecialchars($relatieNaam); ?></h4>
                            <span class="relation-type"><?php echo htmlspecialchars($relatie['RelatieType']); ?></span>
                        </div>
                        <div class="relation-card-body">
                            <?php if($relatie['RelatieType'] == 'Bod'): ?>
                                <p class="bod-bedrag"><i class="fa-solid fa-gavel"></i> € <?php echo number_format($relatie['BodBedrag'], 0, ',', '.'); ?></p>
                                <p><i class="fa-solid fa-hourglass-half"></i> Status: <span class="status-<?php echo strtolower(str_replace(' ', '-', $relatie['BodStatus'])); ?>"><?php echo htmlspecialchars($relatie['BodStatus']); ?></span></p>
                            <?php else: ?>
                                <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($relatie['Telefoonnummer1']); ?></p>
                                <p><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($relatie['Emailadres']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="relation-card-footer">
                            <a href="klanten.php?id=<?php echo $relatie['KlantID']; ?>" class="card-button">Bekijk Klant Volledig</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Nieuwe content voor de '+'-tab -->
                <div class="relation-card" id="tab-add-new">
                    <div class="relation-card-header">
                        <h4>Nieuwe Relatie Toevoegen</h4>
                    </div>
                    <div class="add-new-actions">
                        <a href="bezichtiging_form.php?jacht_id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-calendar-plus"></i> Plan Bezichtiging</a>
                        <a href="bod_form.php?jacht_id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-gavel"></i> Registreer Bod</a>
                        <a href="eigenaar_form.php?jacht_id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-user-check"></i> Koppel Eigenaar</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab-item');
    const cards = document.querySelectorAll('.relation-card');

    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                cards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.getAttribute('data-tab')).classList.add('active');
            });
        });
    }
});
</script>

<?php require 'footer.php'; ?>
