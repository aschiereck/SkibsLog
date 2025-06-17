<?php
$pageTitle = 'Klant Details';
require 'header.php';

// --- Controleer of ID geldig is ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<section class='content-page'><h1>Ongeldig Klant ID</h1></section>"; require 'footer.php'; exit;
}
$klantId = (int)$_GET['id'];

// --- Haal hoofdgegevens op ---
$stmt_klant = $db_connect->prepare("SELECT * FROM Klanten WHERE KlantID = ?");
$stmt_klant->bind_param("i", $klantId);
$stmt_klant->execute();
$result_klant = $stmt_klant->get_result();
if ($result_klant->num_rows === 0) {
    echo "<section class='content-page'><h1>Klant niet gevonden</h1></section>"; require 'footer.php'; exit;
}
$klant = $result_klant->fetch_assoc();

if ($klant['KlantType'] == 'Bedrijf') {
    $klantNaam = htmlspecialchars($klant['Bedrijfsnaam']);
} else {
    $klantNaam = htmlspecialchars($klant['Voornaam'] . ' ' . $klant['Achternaam']);
}
$pageTitle = $klantNaam;
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo $klantNaam; ?> <small>(<?php echo htmlspecialchars($klant['KlantType']); ?>)</small></h2>
    </div>

    <div class="detail-tabs">
        <div class="tab-link-bar">
            <a href="#overzicht" class="tab-link active">Overzicht</a>
            <a href="#communicatie" class="tab-link">Communicatie</a>
        </div>

        <div class="tab-content-area">
            <!-- Laad de inhoud voor elk tabblad uit een apart bestand -->
            <div id="overzicht" class="tab-pane active"><?php include '_klant_tab_overzicht.php'; ?></div>
            <div id="communicatie" class="tab-pane"><?php include '_klant_tab_communicatie.php'; ?></div>
        </div>
    </div>
</section>

<!-- JavaScript voor de tabbladen -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainTabLinks = document.querySelectorAll('.detail-tabs .tab-link');
    const mainTabPanes = document.querySelectorAll('.detail-tabs .tab-pane');

    const activateTab = (hash) => {
        const targetId = hash || '#overzicht';
        const linkToActivate = document.querySelector(`.tab-link[href="${targetId}"]`);
        
        if(linkToActivate) {
            mainTabLinks.forEach(l => l.classList.remove('active'));
            mainTabPanes.forEach(p => p.classList.remove('active'));

            linkToActivate.classList.add('active');
            const targetPane = document.querySelector(targetId);
            if(targetPane) { 
                targetPane.classList.add('active');
            }
        }
    };

    mainTabLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const targetId = this.getAttribute('href');
            window.location.hash = targetId;
            activateTab(targetId);
        });
    });
    
    // Activeer tab op basis van de URL hash bij het laden van de pagina
    activateTab(window.location.hash);
});
</script>

<?php require 'footer.php'; ?>
