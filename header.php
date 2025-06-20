<?php
// Laad de configuratie en start de databaseverbinding
require_once 'config.php';
require_once 'functions.php';

// --- BEVEILIGING: Controleer of de gebruiker is ingelogd ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Haal de gegevens van de ingelogde gebruiker uit de sessie
$huidige_gebruiker = $_SESSION['user_naam'] ?? 'Gebruiker';

// Bepaal de naam van het huidige bestand om de actieve link te markeren
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - SkibsLog' : 'SkibsLog'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    <div class="grid-container">
        <nav class="sidebar" id="sidebar-nav">
            <div class="sidebar-header">
                <!-- AANGEPAST: Font Awesome icoon vervangen door het SVG-logo -->
                <img src="favicon.svg" alt="SkibsLog Logo" class="sidebar-logo">
                <h1>SkibsLog</h1>
            </div>
            <ul class="sidebar-menu">
                <li class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>"><a href="index.php"><i class="fa-solid fa-border-all"></i><span>Dashboard</span></a></li>
                <li class="<?php echo ($currentPage == 'jachten_overzicht.php') ? 'active' : ''; ?>"><a href="jachten_overzicht.php"><i class="fa-solid fa-ship"></i><span>Motorjachten</span></a></li>
                <li class="<?php echo ($currentPage == 'klanten_overzicht.php') ? 'active' : ''; ?>"><a href="klanten_overzicht.php"><i class="fa-solid fa-users"></i><span>Klanten</span></a></li>
                <li class="<?php echo ($currentPage == 'agenda.php') ? 'active' : ''; ?>"><a href="agenda.php"><i class="fa-solid fa-calendar-days"></i><span>Agenda</span></a></li>
                <li class="<?php echo ($currentPage == 'rapportages.php') ? 'active' : ''; ?>"><a href="rapportages.php"><i class="fa-solid fa-chart-pie"></i><span>Rapportages</span></a></li>
                <li class="<?php echo ($currentPage == 'biedingen.php') ? 'active' : ''; ?>"><a href="biedingen.php"><i class="fa-solid fa-comments-dollar"></i><span>Biedingen</span></a></li>
            </ul>
            <div class="sidebar-footer">
                 <?php if (has_role('admin')): ?>
                    <li class="<?php echo ($currentPage == 'instellingen.php') ? 'active' : ''; ?>"><a href="instellingen.php"><i class="fa-solid fa-gear"></i><span>Instellingen</span></a></li>
                 <?php endif; ?>
            </div>
        </nav>
        <main class="main-content">
            <header class="main-header">
                <button class="hamburger-menu" id="hamburger-menu-toggle" aria-label="Open menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <form action="zoek.php" method="get" class="header-search">
                    <button type="submit" class="search-button"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <input type="search" name="q" placeholder="Zoek op naam..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                </form>
                <div class="header-user">
                    <span>Welkom, <?php echo htmlspecialchars($huidige_gebruiker); ?></span>
                    <img src="https://i.pravatar.cc/40?u=<?php echo urlencode($huidige_gebruiker); ?>" alt="Gebruikersfoto">
                    <a href="logout.php" class="logout-button" title="Uitloggen"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </header>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hamburgerToggle = document.getElementById('hamburger-menu-toggle');
    const sidebar = document.getElementById('sidebar-nav');
    const overlay = document.querySelector('.sidebar-overlay');
    function toggleMenu() {
        sidebar.classList.toggle('is-visible');
        overlay.classList.toggle('is-visible');
    }
    if (hamburgerToggle) {
        hamburgerToggle.addEventListener('click', toggleMenu);
    }
    if (overlay) {
        overlay.addEventListener('click', toggleMenu);
    }
});
</script>
