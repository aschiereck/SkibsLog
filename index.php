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

        <main class="main-content">
            
            <header class="main-header">
                <div class="header-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" placeholder="Zoek een jacht, klant of contactlog...">
                </div>
                <div class="header-user">
                    <span>Welkom, <?php echo "Mark de Boer"; // Dynamische naam ?></span>
                    <img src="https://i.pravatar.cc/40?u=markdeboer" alt="Gebruikersfoto">
                    <a href="#" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </header>
            
            <section class="dashboard">
                <h2>Dashboard</h2>
                
                <div class="widgets-grid">
                    
                    <div class="widget">
                        <div class="widget-header">
                            <i class="fa-solid fa-eye"></i>
                            <h3>Vandaag in het vizier</h3>
                        </div>
                        <div class="widget-content">
                            <div class="task-item">
                                <span class="task-time">11:00</span>
                                <p>Bezichtiging 'Blue Spirit' met dhr. Pietersen</p>
                            </div>
                            <div class="task-item">
                                <span class="task-time">14:30</span>
                                <p>Bezichtiging 'Aquastar' met fam. Jansen</p>
                            </div>
                            <div class="task-item">
                                <span class="task-time todo">TODO</span>
                                <p>Terugbellen eigenaar 'Mermaid' over bod</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="widget">
                        <div class="widget-header">
                            <i class="fa-solid fa-timeline"></i>
                            <h3>Recente Activiteit</h3>
                        </div>
                        <div class="widget-content">
                            <div class="activity-item">
                                <p><span class="highlight">Nieuw bod</span> ontvangen op 'Blue Spirit' van Klant C-2005</p>
                                <span class="activity-time">5 min geleden</span>
                            </div>
                             <div class="activity-item">
                                <p><span class="highlight">Prijsaanpassing</span> voor 'Mermaid' van €189.000 naar €182.500</p>
                                <span class="activity-time">1 uur geleden</span>
                            </div>
                            <div class="activity-item">
                                <p><span class="highlight">Nieuwe klant</span> geregistreerd: Fam. Van der Laan</p>
                                <span class="activity-time">3 uur geleden</span>
                            </div>
                        </div>
                    </div>
                    
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

                    <div class="widget widget-stats">
                         <div class="widget-header">
                            <i class="fa-solid fa-briefcase"></i>
                            <h3>Kerncijfers</h3>
                        </div>
                        <div class="widget-content">
                           <div class="stat-item">
                               <h4>Jachten in verkoop</h4>
                               <span>28</span>
                           </div>
                            <div class="stat-item">
                               <h4>Nieuwe leads (maand)</h4>
                               <span>14</span>
                           </div>
                            <div class="stat-item">
                               <h4>Verkocht (kwartaal)</h4>
                               <span>€ 1.2M</span>
                           </div>
                        </div>
                    </div>
                    
                </div>
            </section>
        </main>
        
    </div>
    
</body>
</html>