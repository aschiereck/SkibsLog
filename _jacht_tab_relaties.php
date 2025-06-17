<?php
// Deze variabelen zijn beschikbaar vanuit jachten.php: $jachtId, $db_connect

// Haal alle relaties op voor dit jacht
$stmt_relaties = $db_connect->prepare("
    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Huidige Eigenaar' as RelatieType, ksr.Startdatum as RelatieDatum, NULL as BodBedrag, NULL as BodStatus
    FROM KlantSchipRelaties ksr JOIN Klanten k ON ksr.KlantID = k.KlantID WHERE ksr.SchipID = ? AND ksr.RelatieType = 'Huidige Eigenaar'
    UNION
    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Ex-Eigenaar' as RelatieType, ksr.Einddatum as RelatieDatum, NULL, NULL
    FROM KlantSchipRelaties ksr JOIN Klanten k ON ksr.KlantID = k.KlantID WHERE ksr.SchipID = ? AND ksr.RelatieType = 'Ex-Eigenaar'
    UNION
    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Geïnteresseerd' as RelatieType, b.Datum as RelatieDatum, NULL, NULL
    FROM Bezichtigingen b JOIN Klanten k ON b.KlantID = k.KlantID WHERE b.SchipID = ?
    UNION
    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType, k.Telefoonnummer1, k.Emailadres, 'Bod' as RelatieType, bl.DatumTijdBod as RelatieDatum, bl.BodBedrag, bl.Status as BodStatus
    FROM BiedingenLog bl JOIN Klanten k ON bl.KlantID = k.KlantID WHERE bl.SchipID = ?
    ORDER BY FIELD(RelatieType, 'Huidige Eigenaar', 'Bod', 'Geïnteresseerd', 'Ex-Eigenaar'), RelatieDatum DESC
");
$stmt_relaties->bind_param("iiii", $jachtId, $jachtId, $jachtId, $jachtId);
$stmt_relaties->execute();
$relaties = $stmt_relaties->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div id="jachtRelatiesContainer">
    <div class="card-header-with-action" style="padding: 0 1rem; margin-bottom: 0.5rem;">
        <h4>Relaties</h4>
    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('jachtRelatiesContainer');
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
