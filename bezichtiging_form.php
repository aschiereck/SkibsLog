<?php
$pageTitle = 'Beheer Bod';
require 'header.php';

// --- ROLBEVEILIGING ---
if (!has_role('user')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bewerken.</div></section>";
    require 'footer.php';
    exit;
}

// Initialiseer variabelen
$bod = [
    'BodID' => '', 'SchipID' => '', 'KlantID' => '', 'DatumTijdBod' => date('Y-m-d H:i:s'),
    'BodBedrag' => '', 'Status' => 'In behandeling', 'GeldigTot' => '', 'Voorwaarden' => ''
];
$formAction = 'Toevoegen';
$errors = [];

// --- GET-request verwerken om jacht/klant vooraf in te vullen ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['jacht_id'])) {
        $bod['SchipID'] = (int)$_GET['jacht_id'];
    }
    if (isset($_GET['klant_id'])) {
        $bod['KlantID'] = (int)$_GET['klant_id'];
    }
}

// --- Haal alle jachten en klanten op voor de dropdowns ---
$result_jachten = $db_connect->query("SELECT SchipID, NaamSchip, MerkWerf FROM Schepen WHERE Status = 'Te Koop' OR Status = 'Onder Bod' ORDER BY NaamSchip");
$result_klanten = $db_connect->query("SELECT KlantID, Voornaam, Achternaam, Bedrijfsnaam, KlantType FROM Klanten ORDER BY Achternaam, Bedrijfsnaam");

// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_role('user')) { die('Ongeautoriseerde actie.'); }

    $bodId = $_POST['BodID'] ?? null;
    $bod['SchipID'] = (int)$_POST['SchipID'];
    $bod['KlantID'] = (int)$_POST['KlantID'];
    $bod['DatumTijdBod'] = date('Y-m-d H:i:s');
    $bod['BodBedrag'] = (float)str_replace([',', '.'], ['', '.'], $_POST['BodBedrag']);
    $bod['Status'] = $_POST['Status'];
    $bod['GeldigTot'] = !empty($_POST['GeldigTot']) ? $_POST['GeldigTot'] : null;
    $bod['Voorwaarden'] = trim($_POST['Voorwaarden']);

    // Validatie
    if (empty($bod['SchipID'])) $errors[] = "Selecteer een jacht.";
    if (empty($bod['KlantID'])) $errors[] = "Selecteer een klant.";
    if (empty($bod['BodBedrag']) || $bod['BodBedrag'] <= 0) $errors[] = "Voer een geldig bodbedrag in.";

    if (empty($errors)) {
        if ($bodId) { // --- UPDATE ---
            $stmt = $db_connect->prepare("UPDATE BiedingenLog SET SchipID=?, KlantID=?, BodBedrag=?, Status=?, GeldigTot=?, Voorwaarden=? WHERE BodID=?");
            $stmt->bind_param("iissssi", $bod['SchipID'], $bod['KlantID'], $bod['BodBedrag'], $bod['Status'], $bod['GeldigTot'], $bod['Voorwaarden'], $bodId);
        } else { // --- INSERT ---
            $stmt = $db_connect->prepare("INSERT INTO BiedingenLog (SchipID, KlantID, DatumTijdBod, BodBedrag, Status, GeldigTot, Voorwaarden) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdsss", $bod['SchipID'], $bod['KlantID'], $bod['DatumTijdBod'], $bod['BodBedrag'], $bod['Status'], $bod['GeldigTot'], $bod['Voorwaarden']);
        }

        if ($stmt->execute()) {
            header("Location: biedingen.php");
            exit;
        } else {
            $errors[] = "Databasefout: " . $stmt->error;
        }
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) { // --- GET-request (data ophalen voor wijzigen) ---
    $formAction = 'Wijzigen';
    $bodId = (int)$_GET['id'];
    $stmt = $db_connect->prepare("SELECT * FROM BiedingenLog WHERE BodID = ?");
    $stmt->bind_param("i", $bodId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $bod = $result->fetch_assoc();
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Bod <?php echo $formAction; ?></h2>
        <a href="biedingen.php" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="bod_form.php" method="post">
                <input type="hidden" name="BodID" value="<?php echo htmlspecialchars($bod['BodID']); ?>">
                
                <fieldset>
                    <legend>Betreft</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="SchipID">Jacht</label>
                            <select id="SchipID" name="SchipID" required>
                                <option value="">-- Kies een jacht --</option>
                                <?php mysqli_data_seek($result_jachten, 0); while($jacht = $result_jachten->fetch_assoc()): ?>
                                    <option value="<?php echo $jacht['SchipID']; ?>" <?php echo ($bod['SchipID'] == $jacht['SchipID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($jacht['NaamSchip'] . ' (' . $jacht['MerkWerf'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="KlantID">Bieder</label>
                            <select id="KlantID" name="KlantID" required>
                                <option value="">-- Kies een klant --</option>
                                <?php mysqli_data_seek($result_klanten, 0); while($klant = $result_klanten->fetch_assoc()): 
                                    $klantNaam = ($klant['KlantType'] == 'Bedrijf') ? $klant['Bedrijfsnaam'] : $klant['Voornaam'] . ' ' . $klant['Achternaam'];
                                ?>
                                    <option value="<?php echo $klant['KlantID']; ?>" <?php echo ($bod['KlantID'] == $klant['KlantID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($klantNaam); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Bod Details</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="BodBedrag">Bod (€)</label>
                            <input type="text" id="BodBedrag" name="BodBedrag" value="<?php echo htmlspecialchars($bod['BodBedrag']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="GeldigTot">Geldig tot</label>
                            <input type="date" id="GeldigTot" name="GeldigTot" value="<?php echo htmlspecialchars($bod['GeldigTot']); ?>">
                        </div>
                        <div class="form-group form-group-full">
                            <label for="Status">Status</label>
                            <select id="Status" name="Status">
                                <option value="In behandeling" <?php echo ($bod['Status'] == 'In behandeling') ? 'selected' : ''; ?>>In behandeling</option>
                                <option value="Geaccepteerd" <?php echo ($bod['Status'] == 'Geaccepteerd') ? 'selected' : ''; ?>>Geaccepteerd</option>
                                <option value="Afgewezen" <?php echo ($bod['Status'] == 'Afgewezen') ? 'selected' : ''; ?>>Afgewezen</option>
                                <option value="Ingetrokken door bieder" <?php echo ($bod['Status'] == 'Ingetrokken door bieder') ? 'selected' : ''; ?>>Ingetrokken door bieder</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Voorwaarden</legend>
                     <div class="form-group form-group-full">
                        <label for="Voorwaarden">(Ontbindende) voorwaarden</label>
                        <textarea id="Voorwaarden" name="Voorwaarden" rows="4" placeholder="Bijv. onder voorbehoud van technische keuring en financiering."><?php echo htmlspecialchars($bod['Voorwaarden']); ?></textarea>
                    </div>
                </fieldset>

                <div class="form-group form-group-full">
                    <button type="submit" class="action-button-header" <?php if (!has_role('user')) echo 'disabled'; ?>>
                        <?php echo $formAction; ?> Bod
                    </button>
                </div>
            </form>
        </div>
        <div class="form-sidebar">
            <div class="info-card">
                <h3><i class="fa-solid fa-gavel"></i> Een bod beheren</h3>
                <p>Het ontvangen van een bod is een cruciaal moment in het verkoopproces. Documenteer alle details zorgvuldig.</p>
                <ul>
                    <li>Selecteer het juiste <strong>jacht</strong> en de <strong>bieder</strong>.</li>
                    <li>Voer het exacte bedrag in zonder leestekens.</li>
                    <li>Noteer eventuele voorwaarden duidelijk. Dit voorkomt misverstanden in een later stadium.</li>
                </ul>
                <p>De status van het bod kan hier worden bijgewerkt nadat u met de verkoper heeft gesproken.</p>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
