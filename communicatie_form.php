<?php
$pageTitle = 'Communicatie Loggen';
require 'header.php';

// --- ROLBEVEILIGING ---
if (!has_role('user')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bewerken.</div></section>";
    require 'footer.php';
    exit;
}

// Initialiseer variabelen
$log = ['LogID' => '', 'KlantID' => '', 'DatumTijd' => date('Y-m-d H:i:s'), 'Type' => 'Telefoon (uitgaand)', 'Onderwerp' => '', 'Notities' => '', 'DocumentLink' => '', 'GerelateerdSchipID' => null, 'MedewerkerNaam' => $huidige_gebruiker];
$klantId = $_GET['klant_id'] ?? null;
$logId = $_GET['id'] ?? null;
$formAction = 'Toevoegen';

// Data ophalen voor wijzigen
if ($logId) {
    // Logica voor wijzigen...
} elseif ($klantId) {
    $log['KlantID'] = $klantId;
}

// --- Haal data op voor dropdowns ---
$result_klanten = $db_connect->query("SELECT KlantID, Voornaam, Achternaam FROM Klanten ORDER BY Achternaam");
$result_jachten = $db_connect->query("SELECT SchipID, NaamSchip FROM Schepen ORDER BY NaamSchip");


// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... Logica voor opslaan hier ...
    header("Location: klanten.php?id=" . (int)$_POST['KlantID'] . "#communicatie");
    exit;
}
?>
<section class="content-page">
    <div class="page-header">
        <h2>Communicatie <?php echo $formAction; ?></h2>
        <a href="javascript:history.back()" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="communicatie_form.php" method="post">
                <input type="hidden" name="LogID" value="<?php echo htmlspecialchars($log['LogID']); ?>">
                <fieldset>
                    <legend>Log Details</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="KlantID">Klant</label>
                            <select id="KlantID" name="KlantID" required>
                                <?php while($klant = $result_klanten->fetch_assoc()): ?>
                                    <option value="<?php echo $klant['KlantID']; ?>" <?php echo ($log['KlantID'] == $klant['KlantID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($klant['Voornaam'] . ' ' . $klant['Achternaam']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Type">Type Communicatie</label>
                             <select id="Type" name="Type">
                                <option>Telefoon (uitgaand)</option>
                                <option>Telefoon (inkomend)</option>
                                <option>E-mail</option>
                                <option>Document</option>
                                <option>Opdracht</option>
                            </select>
                        </div>
                        <div class="form-group form-group-full"><label for="Onderwerp">Onderwerp</label><input type="text" id="Onderwerp" name="Onderwerp" required></div>
                        <div class="form-group form-group-full"><label for="Notities">Notities</label><textarea id="Notities" name="Notities" rows="5"></textarea></div>
                        <div class="form-group"><label for="GerelateerdSchipID">Betreft Schip (optioneel)</label>
                            <select id="GerelateerdSchipID" name="GerelateerdSchipID">
                                <option value="">-- Geen specifiek schip --</option>
                                <?php while($jacht = $result_jachten->fetch_assoc()): ?>
                                    <option value="<?php echo $jacht['SchipID']; ?>"><?php echo htmlspecialchars($jacht['NaamSchip']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group form-group-full">
                    <button type="submit" class="action-button-header">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</section>
<?php require 'footer.php'; ?>
