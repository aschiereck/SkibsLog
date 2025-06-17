<?php
// Start de sessie om toegang te krijgen tot sessievariabelen
session_start();

// Verwijder alle sessievariabelen
$_SESSION = array();

// Vernietig de sessie
session_destroy();

// Stuur de gebruiker terug naar de loginpagina
header("location: login.php");
exit;
?>
