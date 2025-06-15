<?php
$pageTitle = 'Klant Details';
require 'header.php';

// Controleer of er een ID is meegegeven in de URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Ongeldig Klant ID</h1><p>Er is geen geldig ID voor een klant opgegeven.</p>";
    require 'footer.php';
    exit;
}

$klantId = (int)$_GET['id'];

// --- Haal de hoofdgegevens van de klant op ---
$stmt = $db_connect->prepare("SELECT * FROM Klanten WHERE KlantID = ?");
$stmt->bind_param("i", $klantId);
$stmt->execute();
$result_klant = $stmt->get_result();

if ($result_klant->num_rows === 0) {
    echo "<h1>Klant niet gevonden</h1><p>Er kon geen klant worden gevonden met ID: " . htmlspecialchars($klantId) . "</p>";
    require 'footer.php';
    exit;
}

$klant = $result_klant->fetch_assoc();

// Bepaal de naam voor de paginatitel
if ($klant['KlantType'] == 'Bedrijf') {
    $pageTitle = htmlspecialchars($klant['Bedrijfsnaam']);
} else {
    $pageTitle = htmlspecialchars($klant['Voornaam'] . ' ' . $klant['Achternaam']);
}

// --- Haal gerelateerde jachten op (via biedingen en bezichtigingen) ---
$stmt_jachten = $db_connect->prepare("
    SELECT DISTINCT s.SchipID, s.NaamSchip, s.MerkWerf, s.ModelType
    FROM Schepen s
    LEFT JOIN BiedingenLog bl ON s.SchipID = bl.SchipID
    LEFT JOIN Bezichtigingen b ON s.SchipID = b.SchipID
    WHERE bl.KlantID = ? OR b.KlantID = ?
");
$stmt_jachten->bind_param("ii", $klantId, $klantId);
$stmt_jachten->execute();
$result_jachten = $stmt_jachten->get_result();

?>

<section class="content-page">
    <div class="page-header">
        <h2>Detailoverzicht: <?php echo $pageTitle; ?></h2>
        <a href="klant_form.php?id=<?php echo $klantId; ?>" class="action-button-header"><i class="fa-solid fa-pencil"></i> Wijzigen</a>
    </div>

    <div class="detail-grid">
        <!-- Kaart met contactinformatie -->
        <div class="detail-card">
            <h3>Contactgegevens</h3>
            <ul>
                <li><strong>Type:</strong> <?php echo htmlspecialchars($klant['KlantType']); ?></li>
                <li><strong>Adres:</strong> <?php echo htmlspecialchars($klant['Adres']); ?></li>
                <li><strong>Woonplaats:</strong> <?php echo htmlspecialchars($klant['Postcode'] . ' ' . $klant['Woonplaats']); ?></li>
                <li><strong>Telefoon:</strong> <?php echo htmlspecialchars($klant['Telefoonnummer1']); ?></li>
                <li><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($klant['Emailadres']); ?>"><?php echo htmlspecialchars($klant['Emailadres']); ?></a></li>
            </ul>
        </div>
        
        <!-- Kaart met notities -->
        <div class="detail-card">
            <h3>Notities</h3>
            <p><?php echo nl2br(htmlspecialchars($klant['Notities'])); ?></p>
        </div>

        <!-- Kaart met gerelateerde jachten -->
        <div class="detail-card detail-card-full-width">
            <h3>Gerelateerde Jachten</h3>
            <?php if ($result_jachten->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Naam Jacht</th><th>Merk & Model</th><th>Actie</th></tr>
                    </thead>
                    <tbody>
                        <?php while($jacht = $result_jachten->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $jacht['SchipID']; ?></td>
                                <td><?php echo htmlspecialchars($jacht['NaamSchip']); ?></td>
                                <td><?php echo htmlspecialchars($jacht['MerkWerf'] . ' ' . $jacht['ModelType']); ?></td>
                                <td class="actions"><a href="jacht_detail.php?id=<?php echo $jacht['SchipID']; ?>" title="Bekijk Jacht"><i class="fa-solid fa-ship"></i></a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Deze klant is nog niet aan een jacht gekoppeld.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
