<?php
// Deze variabelen zijn beschikbaar vanuit klanten.php: $klant, $klantId, $klantNaam, $db_connect
?>

<div class="interactive-container">
    <div class="main-card-container">
        <div class="main-card">
            <div class="card-header-with-action">
                <h3>Contactgegevens</h3>
                <?php if (has_role('user')): ?>
                <a href="klant_form.php?id=<?php echo $klantId; ?>" class="card-action-icon" title="Klantgegevens wijzigen"><i class="fa-solid fa-pencil"></i></a>
                <?php endif; ?>
            </div>
            <p class="main-card-subtitle"><?php echo htmlspecialchars($klant['KlantType']); ?></p>
            <div class="main-card-contact">
                <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($klant['Adres'] . ', ' . $klant['Postcode'] . ' ' . $klant['Woonplaats'] . ', ' . $klant['Land']); ?></p>
                <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($klant['Telefoonnummer1']); ?></p>
                <p><i class="fa-solid fa-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($klant['Emailadres']); ?>"><?php echo htmlspecialchars($klant['Emailadres']); ?></a></p>
            </div>
            <h4>Notities</h4>
            <p class="main-card-description"><?php echo nl2br(htmlspecialchars($klant['Notities'])); ?></p>
        </div>
    </div>
    
    <div class="tabs-container" id="klantRelatiesContainer">
        <?php
        // Laad de gerelateerde schepen uit het nieuwe, aparte bestand
        include '_klant_tab_relaties.php';
        ?>
    </div>
</div>
