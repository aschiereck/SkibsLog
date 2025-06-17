<?php
// Deze variabelen zijn beschikbaar vanuit _klant_tab_overzicht.php: $klantId, $db_connect

// Haal ALLE gerelateerde schepen op
$stmt_relaties = $db_connect->prepare("
    SELECT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType, s.Vraagprijs, s.Status, 'Huidige Eigenaar' as RelatieType, ksr.Startdatum as RelatieDatum
    FROM KlantSchipRelaties ksr JOIN Schepen s ON ksr.SchipID = s.SchipID WHERE ksr.KlantID = ? AND ksr.RelatieType = 'Huidige Eigenaar'
    UNION
    SELECT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType, s.Vraagprijs, s.Status, 'Ex-Eigenaar' as RelatieType, ksr.Einddatum as RelatieDatum
    FROM KlantSchipRelaties ksr JOIN Schepen s ON ksr.SchipID = s.SchipID WHERE ksr.KlantID = ? AND ksr.RelatieType = 'Ex-Eigenaar'
    UNION
    SELECT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType, s.Vraagprijs, s.Status, 'Geïnteresseerd' as RelatieType, b.Datum as RelatieDatum
    FROM Bezichtigingen b JOIN Schepen s ON b.SchipID = s.SchipID WHERE b.KlantID = ?
    UNION
    SELECT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType, s.Vraagprijs, s.Status, 'Bod' as RelatieType, bl.DatumTijdBod as RelatieDatum
    FROM BiedingenLog bl JOIN Schepen s ON bl.SchipID = s.SchipID WHERE bl.KlantID = ?
    ORDER BY FIELD(RelatieType, 'Huidige Eigenaar', 'Bod', 'Geïnteresseerd', 'Ex-Eigenaar'), RelatieDatum DESC
");
$stmt_relaties->bind_param("iiii", $klantId, $klantId, $klantId, $klantId);
$stmt_relaties->execute();
$relaties = $stmt_relaties->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="card-header-with-action" style="padding: 0 1rem; margin-bottom: 0.5rem;">
    <h4>Gerelateerde Schepen</h4>
</div>
<ul class="tab-list">
    <?php foreach ($relaties as $index => $relatie): 
        $relatieClass = 'rel-' . strtolower(str_replace(' ', '-', $relatie['RelatieType']));
    ?>
        <li class="tab-item <?php echo $relatieClass; ?> <?php echo ($index == 0) ? 'active' : ''; ?>" data-tab="tab-schip-<?php echo $relatie['SchipID'].'-'.$index; ?>">
            <?php echo htmlspecialchars($relatie['NaamSchip']); ?>
        </li>
    <?php endforeach; ?>
    <?php if (has_role('user')): ?>
        <li class="tab-item add-new-tab" data-tab="tab-schip-add-new"><i class="fa-solid fa-plus"></i></li>
    <?php endif; ?>
</ul>
<div class="tab-content">
    <?php foreach ($relaties as $index => $relatie):
        $relatieClass = 'rel-' . strtolower(str_replace(' ', '-', $relatie['RelatieType']));
    ?>
        <div class="relation-card <?php echo $relatieClass; ?> <?php echo ($index == 0) ? 'active' : ''; ?>" id="tab-schip-<?php echo $relatie['SchipID'].'-'.$index; ?>">
            <div class="relation-card-header">
                <h4><?php echo htmlspecialchars($relatie['MerkWerf'] . ' ' . $relatie['ModelType']); ?></h4>
                <span class="relation-type"><?php echo htmlspecialchars($relatie['RelatieType']); ?></span>
            </div>
            <div class="relation-card-body">
                <p><strong>Status:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $relatie['Status'])); ?>"><?php echo htmlspecialchars($relatie['Status']); ?></span></p>
                <p><strong>Vraagprijs:</strong> € <?php echo number_format($relatie['Vraagprijs'], 0, ',', '.'); ?></p>
            </div>
            <div class="relation-card-footer">
                <a href="jachten.php?id=<?php echo $relatie['SchipID']; ?>" class="card-button">Bekijk Jacht Volledig</a>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="relation-card" id="tab-schip-add-new">
        <div class="relation-card-header"><h4>Nieuwe Schiprelatie</h4></div>
        <div class="add-new-actions">
            <!-- DEZE LINK IS GECORRIGEERD -->
            <a href="bezichtiging_form.php?klant_id=<?php echo $klantId; ?>" class="action-button-header"><i class="fa-solid fa-calendar-plus"></i> Plan Bezichtiging</a>
            <a href="bod_form.php?klant_id=<?php echo $klantId; ?>" class="action-button-header"><i class="fa-solid fa-gavel"></i> Registreer Bod</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gebruik de unieke container ID van _klant_tab_overzicht.php
    const container = document.getElementById('klantRelatiesContainer');
    if (!container) return;

    container.addEventListener('click', function(event) {
        const clickedTab = event.target.closest('.tab-item');
        if (!clickedTab) return;

        const allTabs = container.querySelectorAll('.tab-item');
        const allCards = container.querySelectorAll('.relation-card');
        
        allTabs.forEach(t => t.classList.remove('active'));
        allCards.forEach(c => c.classList.remove('active'));

        clickedTab.classList.add('active');
        const targetCard = container.querySelector('#' + clickedTab.dataset.tab);
        if (targetCard) {
            targetCard.classList.add('active');
        }
    });
});
</script>
