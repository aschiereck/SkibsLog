<?php
$pageTitle = 'Jacht Details';
require 'header.php';

// Controleer of er een ID is meegegeven in de URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Ongeldig Jacht ID</h1><p>Er is geen geldig ID voor een jacht opgegeven.</p>";
    require 'footer.php';
    exit; // Stop het script
}

$jachtId = (int)$_GET['id'];

// --- Haal de hoofdgegevens van het jacht op ---
// Dit blijft een simpele query naar de Schepen tabel
$stmt_jacht = $db_connect->prepare("SELECT * FROM Schepen WHERE SchipID = ?");
$stmt_jacht->bind_param("i", $jachtId);
$stmt_jacht->execute();
$result_jacht = $stmt_jacht->get_result();

if ($result_jacht->num_rows === 0) {
    echo "<h1>Jacht niet gevonden</h1><p>Er kon geen jacht worden gevonden met ID: " . htmlspecialchars($jachtId) . "</p>";
    require 'footer.php';
    exit;
}
$jacht = $result_jacht->fetch_assoc();
$pageTitle = htmlspecialchars($jacht['NaamSchip']);

// --- AANGEPAST: Haal ALLE huidige eigenaren op via de nieuwe koppeltabel ---
$stmt_eigenaren = $db_connect->prepare("
    SELECT k.KlantID, k.Voornaam, k.Achternaam, k.Bedrijfsnaam, k.KlantType
    FROM KlantSchipRelaties ksr
    JOIN Klanten k ON ksr.KlantID = k.KlantID
    WHERE ksr.SchipID = ? AND ksr.RelatieType = 'Huidige Eigenaar'
");
$stmt_eigenaren->bind_param("i", $jachtId);
$stmt_eigenaren->execute();
$result_eigenaren = $stmt_eigenaren->get_result();


// --- De rest van de queries blijven hetzelfde ---
// --- Haal gerelateerde bezichtigingen op ---
$stmt_bezichtigingen = $db_connect->prepare("
    SELECT b.*, k.Voornaam, k.Achternaam 
    FROM Bezichtigingen b
    JOIN Klanten k ON b.KlantID = k.KlantID
    WHERE b.SchipID = ? ORDER BY b.Datum DESC
");
$stmt_bezichtigingen->bind_param("i", $jachtId);
$stmt_bezichtigingen->execute();
$result_bezichtigingen = $stmt_bezichtigingen->get_result();

// --- Haal gerelateerde biedingen op ---
$stmt_biedingen = $db_connect->prepare("
    SELECT bl.*, k.Voornaam, k.Achternaam
    FROM BiedingenLog bl
    JOIN Klanten k ON bl.KlantID = k.KlantID
    WHERE bl.SchipID = ? ORDER BY bl.DatumTijdBod DESC
");
$stmt_biedingen->bind_param("i", $jachtId);
$stmt_biedingen->execute();
$result_biedingen = $stmt_biedingen->get_result();

?>

<section class="content-page">
    <div class="page-header">
        <h2>Detailoverzicht: <?php echo htmlspecialchars($jacht['NaamSchip']); ?></h2>
        <div>
            <a href="jacht_form.php?id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-pencil"></i> Wijzigen</a>
            <a href="jacht_acties.php?actie=verwijder&id=<?php echo $jachtId; ?>" class="action-button-header-delete" onclick="return confirm('Weet u zeker dat u dit jacht wilt verwijderen?');"><i class="fa-solid fa-trash"></i> Verwijderen</a>
        </div>
    </div>

    <div class="detail-grid">
        <!-- Kaart met hoofdinformatie -->
        <div class="detail-card">
            <h3>Specificaties</h3>
            <ul>
                <li><strong>Merk & Model:</strong> <?php echo htmlspecialchars($jacht['MerkWerf'] . ' ' . $jacht['ModelType']); ?></li>
                <li><strong>Bouwjaar:</strong> <?php echo $jacht['Bouwjaar']; ?></li>
                <li><strong>Status:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $jacht['Status'])); ?>"><?php echo htmlspecialchars($jacht['Status']); ?></span></li>
                <li><strong>Vraagprijs:</strong> € <?php echo number_format($jacht['Vraagprijs'], 2, ',', '.'); ?></li>
                <li><strong>Ligplaats:</strong> <?php echo htmlspecialchars($jacht['Ligplaats']); ?></li>
                <li><strong>Afmetingen:</strong> <?php echo $jacht['Lengte']; ?>m x <?php echo $jacht['Breedte']; ?>m x <?php echo $jacht['Diepgang']; ?>m</li>
                <li><strong>BTW Status:</strong> <?php echo htmlspecialchars($jacht['BTWStatus']); ?></li>
            </ul>
        </div>
        
        <!-- AANGEPAST: Kaart met Eigenaar informatie die meerdere eigenaren kan tonen -->
        <div class="detail-card">
            <h3>Huidige Eigenaar(s)</h3>
            <?php if ($result_eigenaren->num_rows > 0): ?>
                <ul>
                <?php while ($eigenaar = $result_eigenaren->fetch_assoc()): ?>
                    <?php
                        $eigenaarNaam = ($eigenaar['KlantType'] == 'Bedrijf') 
                            ? $eigenaar['Bedrijfsnaam'] 
                            : $eigenaar['Voornaam'] . ' ' . $eigenaar['Achternaam'];
                    ?>
                    <li>
                        <strong><a href="klant_detail.php?id=<?php echo $eigenaar['KlantID']; ?>"><?php echo htmlspecialchars($eigenaarNaam); ?></a></strong>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>De eigenaar van dit schip is niet in het systeem geregistreerd.</p>
            <?php endif; ?>
        </div>
        
        <!-- Kaart met omschrijving (nu over de volledige breedte) -->
        <div class="detail-card detail-card-full-width">
            <h3>Omschrijving</h3>
            <p><?php echo nl2br(htmlspecialchars($jacht['OmschrijvingAlg'])); ?></p>
        </div>

        <!-- Kaart met bezichtigingen -->
        <div class="detail-card">
            <h3>Geplande Bezichtigingen</h3>
            <?php if ($result_bezichtigingen->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr><th>Datum</th><th>Klant</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while($item = $result_bezichtigingen->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($item['Datum'])); ?></td>
                                <td><?php echo htmlspecialchars($item['Voornaam'] . ' ' . $item['Achternaam']); ?></td>
                                <td><?php echo htmlspecialchars($item['Status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nog geen bezichtigingen gepland.</p>
            <?php endif; ?>
        </div>
        
        <!-- Kaart met biedingen -->
        <div class="detail-card">
            <h3>Recente Biedingen</h3>
             <?php if ($result_biedingen->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr><th>Datum</th><th>Klant</th><th>Bedrag</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while($item = $result_biedingen->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d-m-Y H:i', strtotime($item['DatumTijdBod'])); ?></td>
                                <td><?php echo htmlspecialchars($item['Voornaam'] . ' ' . $item['Achternaam']); ?></td>
                                <td>€ <?php echo number_format($item['BodBedrag'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($item['Status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nog geen biedingen ontvangen.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
