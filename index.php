<?php
// Laad de configuratie en maak verbinding met de database.
require_once 'config.php';

// --- DATA OPHALEN VOOR DASHBOARD ---

// Voorbeeld: Huidige gebruiker
$huidige_gebruiker = "Mark de Boer";

// --- WIDGET 1: VANDAAG IN HET VIZIER (Bezichtigingen & Taken) ---
$vandaag = date('Y-m-d');
$query_vandaag = "SELECT Tijd, Status, SchipID, KlantID FROM Bezichtigingen WHERE Datum = '{$vandaag}' AND Status = 'Gepland' ORDER BY Tijd ASC";
$result_vandaag = mysqli_query($db_connect, $query_vandaag);
// TODO: Voeg hier logica toe voor handmatige taken.

// --- WIDGET 2: RECENTE ACTIVITEIT ---
// Deze query combineert meerdere gebeurtenissen en sorteert ze op datum.
$query_activiteit = "
    (SELECT 'Nieuw bod' as Type, b.DatumTijdBod as Datum, s.NaamSchip, k.Achternaam 
     FROM BiedingenLog b 
     JOIN Schepen s ON b.SchipID = s.SchipID 
     JOIN Klanten k ON b.KlantID = k.KlantID)
    UNION
    (SELECT 'Nieuwe klant' as Type, kcl.DatumTijd as Datum, '' as NaamSchip, k.Achternaam
     FROM KlantContactLog kcl
     JOIN Klanten k ON kcl.KlantID = k.KlantID
     WHERE kcl.Onderwerp LIKE '%Nieuwe klant geregistreerd%')
    ORDER BY Datum DESC
    LIMIT 3";
$result_activiteit = mysqli_query($db_connect, $query_activiteit);


// --- WIDGET 4: KERNCIJFERS ---
// Jachten in verkoop
$query_jachten_verkoop = "SELECT COUNT(SchipID) as aantal FROM Schepen WHERE Status = 'Te Koop'";
$result_jachten_verkoop = mysqli_query($db_connect, $query_jachten_verkoop);
$aantal_jachten = mysqli_fetch_assoc($result_jachten_verkoop)['aantal'];

// Nieuwe leads deze maand
$maand_start = date('Y-m-01');
$query_leads_maand = "SELECT COUNT(KlantID) as aantal FROM Klanten WHERE KlantID IN (SELECT KlantID FROM KlantContactLog WHERE DatumTijd >= '{$maand_start}')";
$result_leads_maand = mysqli_query($db_connect, $query_leads_maand);
$aantal_leads = mysqli_fetch_assoc($result_leads_maand)['aantal'];

// Verkocht dit kwartaal
$kwartaal_start = date('Y-m-d', strtotime('first day of this quarter'));
$query_verkocht_kwartaal = "SELECT SUM(UiteindelijkeVerkoopprijs) as totaal FROM Schepen WHERE DatumVerkocht >= '{$kwartaal_start}'";
$result_verkocht_kwartaal = mysqli_query($db_connect, $query_verkocht_kwartaal);
$totaal_verkocht = mysqli_fetch_assoc($result_verkocht_kwartaal)['totaal'];

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SkibsLog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <div class="grid-container">
        
        <!-- Sidebar (geen wijzigingen) -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-anchor"></i>
                <h1>SkibsLog</h1>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="#"><i class="fa-solid fa-border-all"></i><span>Dashboard</span></a></li>
                <li><a href="#"><i class="fa-solid fa-ship"></i><span>Motorjachten</span></a></li>
                <li><a href="#"><i class="fa-solid fa-users"></i><span>Klanten</span></a></li>
                <li><a href="#"><i class="fa-solid fa-calendar-days"></i><span>Agenda</span></a></li>
                <li><a href="#"><i class="fa-solid fa-chart-pie"></i><span>Rapportages</span></a></li>
                <li><a href="#"><i class="fa-solid fa-comments-dollar"></i><span>Biedingen</span></a></li>
            </ul>
            <div class="sidebar-footer">
                <a href="#"><i class="fa-solid fa-gear"></i><span>Instellingen</span></a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            
            <header class="main-header">
                <div class="header-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" placeholder="Zoek een jacht, klant of contactlog...">
                </div>
                <div class="header-user">
                    <span>Welkom, <?php echo htmlspecialchars($huidige_gebruiker); ?></span>
                    <img src="https://i.pravatar.cc/40?u=markdeboer" alt="Gebruikersfoto">
                    <a href="#" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </header>
            
            <section class="dashboard">
                <h2>Dashboard</h2>
                
                <div class="widgets-grid">
                    
                    <!-- Widget: Vandaag in het vizier -->
                    <div class="widget">
                        <div class="widget-header">
                            <i class="fa-solid fa-eye"></i>
                            <h3>Vandaag in het vizier</h3>
                        </div>
                        <div class="widget-content">
                            <?php if ($result_vandaag && mysqli_num_rows($result_vandaag) > 0): ?>
                                <?php while($item = mysqli_fetch_assoc($result_vandaag)): ?>
                                    <div class="task-item">
                                        <span class="task-time"><?php echo date('H:i', strtotime($item['Tijd'])); ?></span>
                                        <p>Bezichtiging (Schip #<?php echo $item['SchipID']; ?>) met Klant #<?php echo $item['KlantID']; ?></p>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>Geen geplande bezichtigingen voor vandaag.</p>
                            <?php endif; ?>
                            <!-- Voorbeeld van een taak -->
                            <div class="task-item">
                                <span class="task-time todo">TODO</span>
                                <p>Terugbellen eigenaar 'Mermaid' over bod</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Widget: Recente Activiteit -->
                    <div class="widget">
                        <div class="widget-header">
                            <i class="fa-solid fa-timeline"></i>
                            <h3>Recente Activiteit</h3>
                        </div>
                        <div class="widget-content">
                             <?php if ($result_activiteit && mysqli_num_rows($result_activiteit) > 0): ?>
                                <?php while($item = mysqli_fetch_assoc($result_activiteit)): ?>
                                    <div class="activity-item">
                                        <p><span class="highlight"><?php echo htmlspecialchars($item['Type']); ?></span> 
                                        <?php 
                                            // Toon de juiste tekst per type activiteit
                                            if($item['Type'] == 'Nieuw bod') {
                                                echo "ontvangen op '" . htmlspecialchars($item['NaamSchip']) . "' van dhr. " . htmlspecialchars($item['Achternaam']);
                                            } else {
                                                echo "geregistreerd: " . htmlspecialchars($item['Achternaam']);
                                            }
                                        ?>
                                        </p>
                                        <span class="activity-time"><?php echo date('d M H:i', strtotime($item['Datum'])); ?></span>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>Geen recente activiteit gevonden.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Widget: Snelle Acties (geen wijzigingen) -->
                    <div class="widget widget-actions">
                         <div class="widget-header">
                            <i class="fa-solid fa-bolt"></i>
                            <h3>Snelle Acties</h3>
                        </div>
                        <div class="widget-content">
                           <a href="#" class="action-button"><i class="fa-solid fa-ship"></i> Nieuw Motorjacht Toevoegen</a>
                           <a href="#" class="action-button"><i class="fa-solid fa-user-plus"></i> Nieuwe Klant Registreren</a>
                           <a href="#" class="action-button"><i class="fa-solid fa-calendar-plus"></i> Nieuwe Bezichtiging Plannen</a>
                        </div>
                    </div>

                    <!-- Widget: Kerncijfers -->
                    <div class="widget widget-stats">
                         <div class="widget-header">
                            <i class="fa-solid fa-briefcase"></i>
                            <h3>Kerncijfers</h3>
                        </div>
                        <div class="widget-content">
                           <div class="stat-item">
                               <h4>Jachten in verkoop</h4>
                               <span><?php echo $aantal_jachten; ?></span>
                           </div>
                            <div class="stat-item">
                               <h4>Nieuwe leads (maand)</h4>
                               <span><?php echo $aantal_leads; ?></span>
                           </div>
                            <div class="stat-item">
                               <h4>Verkocht (kwartaal)</h4>
                               <span>€ <?php echo number_format($totaal_verkocht / 1000, 0, ',', '.') . 'K'; ?></span>
                           </div>
                        </div>
                    </div>
                    
                </div>
            </section>
        </main>
        
    </div>
    
</body>
</html>
