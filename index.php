<?php
$pageTitle = 'Dashboard'; // Stel de paginatitel in voor de header
require 'header.php'; // Laad de header

// --- DATA OPHALEN VOOR DASHBOARD (deze logica blijft hier) ---

// --- WIDGET 1: VANDAAG IN HET VIZIER (Bezichtigingen) ---
$vandaag = date('Y-m-d');
$query_vandaag = "
    SELECT b.Tijd, s.NaamSchip, k.Voornaam, k.Achternaam 
    FROM Bezichtigingen b
    JOIN Schepen s ON b.SchipID = s.SchipID
    JOIN Klanten k ON b.KlantID = k.KlantID
    WHERE b.Datum = '{$vandaag}' AND b.Status = 'Gepland' ORDER BY b.Tijd ASC";
$result_vandaag = $db_connect->query($query_vandaag);

// --- WIDGET 2: RECENTE ACTIVITEIT ---
$query_activiteit = "
    (SELECT 'Nieuw bod' as Type, b.DatumTijdBod as Datum, s.NaamSchip, k.Achternaam 
     FROM BiedingenLog b 
     JOIN Schepen s ON b.SchipID = s.SchipID 
     JOIN Klanten k ON b.KlantID = k.KlantID)
    UNION
    (SELECT 'Nieuwe klant' as Type, kcl.DatumTijd as Datum, '' as NaamSchip, k.Achternaam
     FROM KlantContactLog kcl
     JOIN Klanten k ON kcl.KlantID = k.KlantID
     WHERE kcl.Onderwerp LIKE 'Nieuwe klant geregistreerd%')
    ORDER BY Datum DESC
    LIMIT 3";
$result_activiteit = $db_connect->query($query_activiteit);


// --- WIDGET 4: KERNCIJFERS ---
$aantal_jachten = $db_connect->query("SELECT COUNT(SchipID) as aantal FROM Schepen WHERE Status = 'Te Koop'")->fetch_assoc()['aantal'];
$maand_start = date('Y-m-01 00:00:00');
$aantal_leads = $db_connect->query("SELECT COUNT(DISTINCT KlantID) as aantal FROM KlantContactLog WHERE DatumTijd >= '{$maand_start}'")->fetch_assoc()['aantal'];
$kwartaal_start = date('Y-m-d', strtotime('first day of this quarter'));
$totaal_verkocht = $db_connect->query("SELECT SUM(UiteindelijkeVerkoopprijs) as totaal FROM Schepen WHERE DatumVerkocht >= '{$kwartaal_start}'")->fetch_assoc()['totaal'];
?>

<!-- De HTML voor de dashboard-sectie -->
<section class="dashboard">
    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
    
    <div class="widgets-grid">
        
        <!-- Widget: Vandaag in het vizier -->
        <div class="widget">
            <div class="widget-header"><i class="fa-solid fa-eye"></i><h3>Vandaag in het vizier</h3></div>
            <div class="widget-content">
                <?php if ($result_vandaag && $result_vandaag->num_rows > 0): ?>
                    <?php while($item = $result_vandaag->fetch_assoc()): ?>
                        <div class="task-item">
                            <span class="task-time"><?php echo date('H:i', strtotime($item['Tijd'])); ?></span>
                            <p>Bezichtiging '<?php echo htmlspecialchars($item['NaamSchip']); ?>' met <?php echo htmlspecialchars($item['Voornaam'] . ' ' . $item['Achternaam']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Geen geplande bezichtigingen voor vandaag.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Widget: Recente Activiteit -->
        <div class="widget">
            <div class="widget-header"><i class="fa-solid fa-timeline"></i><h3>Recente Activiteit</h3></div>
            <div class="widget-content">
                 <?php if ($result_activiteit && $result_activiteit->num_rows > 0): ?>
                    <?php while($item = $result_activiteit->fetch_assoc()): ?>
                        <div class="activity-item">
                            <p><span class="highlight"><?php echo htmlspecialchars($item['Type']); ?></span> 
                            <?php 
                                if($item['Type'] == 'Nieuw bod') {
                                    echo "op '" . htmlspecialchars($item['NaamSchip']) . "' door " . htmlspecialchars($item['Achternaam']);
                                } else {
                                    echo "geregistreerd: " . htmlspecialchars($item['Achternaam']);
                                }
                            ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Geen recente activiteit gevonden.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Widget: Snelle Acties -->
        <div class="widget widget-actions">
             <div class="widget-header"><i class="fa-solid fa-bolt"></i><h3>Snelle Acties</h3></div>
             <div class="widget-content">
               <a href="jachten.php?actie=nieuw" class="action-button"><i class="fa-solid fa-ship"></i> Nieuw Motorjacht</a>
               <a href="klanten.php?actie=nieuw" class="action-button"><i class="fa-solid fa-user-plus"></i> Nieuwe Klant</a>
               <a href="agenda.php?actie=nieuw" class="action-button"><i class="fa-solid fa-calendar-plus"></i> Nieuwe Bezichtiging</a>
             </div>
        </div>

        <!-- Widget: Kerncijfers -->
        <div class="widget widget-stats">
             <div class="widget-header"><i class="fa-solid fa-briefcase"></i><h3>Kerncijfers</h3></div>
             <div class="widget-content">
               <div class="stat-item"><h4>Jachten in verkoop</h4><span><?php echo $aantal_jachten; ?></span></div>
                <div class="stat-item"><h4>Nieuwe leads (maand)</h4><span><?php echo $aantal_leads; ?></span></div>
                <div class="stat-item"><h4>Verkocht (kwartaal)</h4><span>€ <?php echo number_format($totaal_verkocht / 1000, 0, ',', '.') . 'K'; ?></span></div>
             </div>
        </div>
        
    </div>
</section>

<?php
require 'footer.php'; // Laad de footer
?>
