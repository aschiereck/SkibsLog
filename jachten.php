<?php
$pageTitle = 'Jacht Details';
require 'header.php';

// --- Controleer of ID geldig is ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<section class='content-page'><h1>Ongeldig Jacht ID</h1></section>"; require 'footer.php'; exit;
}
$jachtId = (int)$_GET['id'];

// --- Haal hoofdgegevens op ---
$stmt_jacht = $db_connect->prepare("SELECT * FROM Schepen WHERE SchipID = ?");
$stmt_jacht->bind_param("i", $jachtId);
$stmt_jacht->execute();
$result_jacht = $stmt_jacht->get_result();
if ($result_jacht->num_rows === 0) {
    echo "<section class='content-page'><h1>Jacht niet gevonden</h1></section>"; require 'footer.php'; exit;
}
$jacht = $result_jacht->fetch_assoc();
$pageTitle = htmlspecialchars($jacht['NaamSchip']);
?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($jacht['NaamSchip']); ?> <small>(<?php echo htmlspecialchars($jacht['MerkWerf']); ?>)</small></h2>
    </div>

    <div class="detail-tabs">
        <div class="tab-link-bar">
            <a href="#overzicht" class="tab-link active">Overzicht</a>
            <a href="#techniek" class="tab-link">Techniek</a>
            <a href="#onderhoud" class="tab-link">Onderhoud</a>
            <a href="#communicatie" class="tab-link">Communicatie</a>
            <?php if (has_role('superuser')): ?>
                <a href="#kosten" class="tab-link">Kosten</a>
            <?php endif; ?>
        </div>

        <div class="tab-content-area">
            <!-- Laad de inhoud voor elk tabblad uit een apart bestand -->
            <div id="overzicht" class="tab-pane active"><?php include '_jacht_tab_overzicht.php'; ?></div>
            <div id="techniek" class="tab-pane"><?php include '_jacht_tab_techniek.php'; ?></div>
            <div id="onderhoud" class="tab-pane"><?php include '_jacht_tab_onderhoud.php'; ?></div>
            <div id="communicatie" class="tab-pane"><?php include '_jacht_tab_communicatie.php'; ?></div>
            <?php if (has_role('superuser')): ?>
                <div id="kosten" class="tab-pane"><?php include '_jacht_tab_kosten.php'; ?></div>
            <?php endif; ?>
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
