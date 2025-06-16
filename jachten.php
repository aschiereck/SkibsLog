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

// --- Haal ALLE gerelateerde klanten op ---
$stmt_relaties = $db_connect->prepare("
    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Huidige Eigenaar' as RelatieType, ksr.Startdatum as RelatieDatum
    FROM KlantSchipRelaties ksr
    JOIN Klanten k ON ksr.KlantID = k.KlantID
    WHERE ksr.SchipID = ? AND ksr.RelatieType = 'Huidige Eigenaar'

    UNION

    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Ex-Eigenaar' as RelatieType, ksr.Einddatum as RelatieDatum
    FROM KlantSchipRelaties ksr
    JOIN Klanten k ON ksr.KlantID = k.KlantID
    WHERE ksr.SchipID = ? AND ksr.RelatieType = 'Ex-Eigenaar'

    UNION

    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Geïnteresseerd' as RelatieType, b.Datum as RelatieDatum
    FROM Bezichtigingen b
    JOIN Klanten k ON b.KlantID = k.KlantID
    WHERE b.SchipID = ?

    ORDER BY FIELD(RelatieType, 'Huidige Eigenaar', 'Geïnteresseerd', 'Ex-Eigenaar'), RelatieDatum DESC
");
$stmt_relaties->bind_param("iii", $jachtId, $jachtId, $jachtId);
$stmt_relaties->execute();
$result_relaties = $stmt_relaties->get_result();
$relaties = $result_relaties->fetch_all(MYSQLI_ASSOC);

?>

<section class="content-page">
    <div class="page-header">
        <h2>Detailoverzicht: <?php echo htmlspecialchars($jacht['NaamSchip']); ?></h2>
        <div>
            <a href="jacht_form.php?id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-pencil"></i> Wijzigen</a>
        </div>
    </div>

    <div class="interactive-container">
        <!-- Linkerkolom: Hoofdkaart van het jacht -->
        <div class="main-card-container">
            <div class="main-card">
                <h3><?php echo htmlspecialchars($jacht['MerkWerf'] . ' ' . $jacht['ModelType']); ?></h3>
                <p class="main-card-subtitle"><?php echo $jacht['Bouwjaar']; ?> - <?php echo htmlspecialchars($jacht['Ligplaats']); ?></p>
                <div class="main-card-price">€ <?php echo number_format($jacht['Vraagprijs'], 0, ',', '.'); ?></div>
                <div class="main-card-specs">
                    <span><i class="fa-solid fa-ruler-vertical"></i> <?php echo $jacht['Lengte']; ?>m</span>
                    <span><i class="fa-solid fa-ruler-horizontal"></i> <?php echo $jacht['Breedte']; ?>m</span>
                    <span><i class="fa-solid fa-anchor"></i> <?php echo $jacht['Diepgang']; ?>m</span>
                </div>
                <p class="main-card-description"><?php echo nl2br(htmlspecialchars($jacht['OmschrijvingAlg'])); ?></p>
            </div>
        </div>

        <!-- Rechterkolom: Relatiekaarten met tabs -->
        <div class="tabs-container">
            <?php if (!empty($relaties)): ?>
                <ul class="tab-list">
                    <?php foreach ($relaties as $index => $relatie): ?>
                        <?php
                            $relatieNaam = ($relatie['KlantType'] == 'Bedrijf') ? $relatie['Bedrijfsnaam'] : $relatie['Voornaam'] . ' ' . $relatie['Achternaam'];
                        ?>
                        <li class="tab-item <?php echo ($index == 0) ? 'active' : ''; ?>" data-tab="tab-<?php echo $relatie['KlantID']; ?>">
                            <?php echo htmlspecialchars($relatieNaam); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php foreach ($relaties as $index => $relatie): ?>
                        <?php
                            $relatieNaam = ($relatie['KlantType'] == 'Bedrijf') ? $relatie['Bedrijfsnaam'] : $relatie['Voornaam'] . ' ' . $relatie['Achternaam'];
                            $relatieClass = 'rel-' . strtolower(str_replace(' ', '-', $relatie['RelatieType']));
                        ?>
                        <div class="relation-card <?php echo $relatieClass; ?> <?php echo ($index == 0) ? 'active' : ''; ?>" id="tab-<?php echo $relatie['KlantID']; ?>">
                            <div class="relation-card-header">
                                <h4><?php echo htmlspecialchars($relatieNaam); ?></h4>
                                <span class="relation-type"><?php echo htmlspecialchars($relatie['RelatieType']); ?></span>
                            </div>
                            <div class="relation-card-body">
                                <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($relatie['Telefoonnummer1']); ?></p>
                                <p><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($relatie['Emailadres']); ?></p>
                            </div>
                            <div class="relation-card-footer">
                                <!-- HIER IS DE CORRECTIE AANGEBRACHT -->
                                <a href="klanten.php?id=<?php echo $relatie['KlantID']; ?>" class="card-button">Bekijk Klant Volledig</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Geen relaties gevonden voor dit jacht.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- JavaScript voor de tab-functionaliteit -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab-item');
    const cards = document.querySelectorAll('.relation-card');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // Verwijder 'active' van alle tabs en kaarten
            tabs.forEach(t => t.classList.remove('active'));
            cards.forEach(c => c.classList.remove('active'));

            // Voeg 'active' toe aan de geklikte tab en de bijbehorende kaart
            this.classList.add('active');
            const targetCardId = this.getAttribute('data-tab');
            document.getElementById(targetCardId).classList.add('active');
        });
    });
});
</script>

<?php require 'footer.php'; ?>
