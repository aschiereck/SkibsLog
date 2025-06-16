<?php
$pageTitle = 'Klant Details';
require 'header.php';

// Controleer of er een ID is meegegeven in de URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Ongeldig Klant ID</h1>";
    require 'footer.php';
    exit;
}

$klantId = (int)$_GET['id'];

// --- Haal de hoofdgegevens van de klant op ---
$stmt_klant = $db_connect->prepare("SELECT * FROM Klanten WHERE KlantID = ?");
$stmt_klant->bind_param("i", $klantId);
$stmt_klant->execute();
$result_klant = $stmt_klant->get_result();

if ($result_klant->num_rows === 0) {
    echo "<h1>Klant niet gevonden</h1>";
    require 'footer.php';
    exit;
}
$klant = $result_klant->fetch_assoc();

if ($klant['KlantType'] == 'Bedrijf') {
    $pageTitle = htmlspecialchars($klant['Bedrijfsnaam']);
} else {
    $pageTitle = htmlspecialchars($klant['Voornaam'] . ' ' . $klant['Achternaam']);
}

// --- Haal ALLE gerelateerde schepen op ---
$stmt_relaties = $db_connect->prepare("
    SELECT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType, s.Vraagprijs, s.Status, 'Huidige Eigenaar' as RelatieType, ksr.Startdatum as RelatieDatum
    FROM KlantSchipRelaties ksr
    JOIN Schepen s ON ksr.SchipID = s.SchipID
    WHERE ksr.KlantID = ? AND ksr.RelatieType = 'Huidige Eigenaar'

    UNION

    SELECT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType, s.Vraagprijs, s.Status, 'Ex-Eigenaar' as RelatieType, ksr.Einddatum as RelatieDatum
    FROM KlantSchipRelaties ksr
    JOIN Schepen s ON ksr.SchipID = s.SchipID
    WHERE ksr.KlantID = ? AND ksr.RelatieType = 'Ex-Eigenaar'

    UNION

    SELECT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType, s.Vraagprijs, s.Status, 'Geïnteresseerd' as RelatieType, b.Datum as RelatieDatum
    FROM Bezichtigingen b
    JOIN Schepen s ON b.SchipID = s.SchipID
    WHERE b.KlantID = ?

    ORDER BY FIELD(RelatieType, 'Huidige Eigenaar', 'Geïnteresseerd', 'Ex-Eigenaar'), RelatieDatum DESC
");
$stmt_relaties->bind_param("iii", $klantId, $klantId, $klantId);
$stmt_relaties->execute();
$result_relaties = $stmt_relaties->get_result();
$relaties = $result_relaties->fetch_all(MYSQLI_ASSOC);
?>

<section class="content-page">
    <div class="page-header">
        <h2>Detailoverzicht: <?php echo $pageTitle; ?></h2>
        <div>
            <a href="klant_form.php?id=<?php echo $klantId; ?>" class="action-button-header"><i class="fa-solid fa-pencil"></i> Wijzigen</a>
        </div>
    </div>

    <div class="interactive-container">
        <!-- Linkerkolom: Hoofdkaart van de klant -->
        <div class="main-card-container">
             <div class="main-card">
                <h3><?php echo $pageTitle; ?></h3>
                <p class="main-card-subtitle"><?php echo htmlspecialchars($klant['KlantType']); ?></p>
                <div class="main-card-contact">
                    <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($klant['Adres'] . ', ' . $klant['Woonplaats']); ?></p>
                    <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($klant['Telefoonnummer1']); ?></p>
                    <p><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($klant['Emailadres']); ?></p>
                </div>
                <h4>Notities</h4>
                <p class="main-card-description"><?php echo nl2br(htmlspecialchars($klant['Notities'])); ?></p>
            </div>
        </div>

        <!-- Rechterkolom: Relatiekaarten van schepen met tabs -->
        <div class="tabs-container">
            <?php if (!empty($relaties)): ?>
                <ul class="tab-list">
                    <?php foreach ($relaties as $index => $relatie): ?>
                        <li class="tab-item <?php echo ($index == 0) ? 'active' : ''; ?>" data-tab="tab-<?php echo $relatie['SchipID']; ?>">
                            <?php echo htmlspecialchars($relatie['NaamSchip']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php foreach ($relaties as $index => $relatie): ?>
                        <?php $relatieClass = 'rel-' . strtolower(str_replace(' ', '-', $relatie['RelatieType'])); ?>
                        <div class="relation-card <?php echo $relatieClass; ?> <?php echo ($index == 0) ? 'active' : ''; ?>" id="tab-<?php echo $relatie['SchipID']; ?>">
                            <div class="relation-card-header">
                                <h4><?php echo htmlspecialchars($relatie['NaamSchip']); ?></h4>
                                <span class="relation-type"><?php echo htmlspecialchars($relatie['RelatieType']); ?></span>
                            </div>
                            <div class="relation-card-body">
                                <p><strong><?php echo htmlspecialchars($relatie['MerkWerf'] . ' ' . $relatie['ModelType']); ?></strong></p>
                                <p>Prijs: € <?php echo number_format($relatie['Vraagprijs'], 0, ',', '.'); ?></p>
                            </div>
                            <div class="relation-card-footer">
                                <a href="jachten.php?id=<?php echo $relatie['SchipID']; ?>" class="card-button">Bekijk Jacht Volledig</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Geen schepen gevonden voor deze klant.</p>
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
            tabs.forEach(t => t.classList.remove('active'));
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.getAttribute('data-tab')).classList.add('active');
        });
    });
});
</script>

<?php require 'footer.php'; ?>
