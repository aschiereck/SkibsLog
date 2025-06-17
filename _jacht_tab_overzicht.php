<?php
// Deze variabelen zijn beschikbaar vanuit jachten.php: $jacht, $jachtId, $db_connect

// De $jacht array bevat al alle informatie die we nodig hebben.
?>
<div class="interactive-container">
    <div class="main-card-container">
        <div class="main-card">
            <div class="card-header-with-action">
               <h3>Hoofdgegevens</h3>
               <?php if (has_role('user')): ?>
               <a href="jacht_form.php?id=<?php echo $jachtId; ?>" class="card-action-icon" title="Hoofdgegevens wijzigen"><i class="fa-solid fa-pencil"></i></a>
               <?php endif; ?>
           </div>
           <p class="main-card-subtitle"><?php echo htmlspecialchars($jacht['MerkWerf'] . ' - ' . $jacht['ModelType']); ?></p>
           <div class="main-card-price">€ <?php echo number_format($jacht['Vraagprijs'], 0, ',', '.'); ?></div>
           <div class="main-card-specs">
               <span><i class="fa-solid fa-ruler-vertical"></i> <?php echo htmlspecialchars($jacht['Lengte']); ?>m</span>
               <span><i class="fa-solid fa-ruler-horizontal"></i> <?php echo htmlspecialchars($jacht['Breedte']); ?>m</span>
               <span><i class="fa-solid fa-anchor"></i> <?php echo htmlspecialchars($jacht['Diepgang']); ?>m</span>
           </div>
           <div class="main-card-detaillist">
                <ul>
                    <li><strong>Status:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $jacht['Status'])); ?>"><?php echo htmlspecialchars($jacht['Status']); ?></span></li>
                    <li><strong>Bouwjaar:</strong> <?php echo $jacht['Bouwjaar']; ?></li>
                    <li><strong>Ligplaats:</strong> <?php echo htmlspecialchars($jacht['Ligplaats']); ?></li>
                    <li><strong>BTW Status:</strong> <?php echo htmlspecialchars($jacht['BTWStatus']); ?></li>
                    <li><strong>Materiaal:</strong> <?php echo htmlspecialchars($jacht['MateriaalRomp']); ?></li>
                    <li><strong>Registratienummer:</strong> <?php echo htmlspecialchars($jacht['Registratienummer']); ?></li>
                    <li><strong>Vlag:</strong> <?php echo htmlspecialchars($jacht['Vlag']); ?></li>
                </ul>
            </div>
            <h4>Omschrijving</h4>
            <p class="main-card-description"><?php echo nl2br(htmlspecialchars($jacht['OmschrijvingAlg'])); ?></p>
        </div>
    </div>
    <div class="tabs-container">
        <?php include '_jacht_tab_relaties.php'; ?>
    </div>
</div>
