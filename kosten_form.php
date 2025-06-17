<?php
$pageTitle = 'Beheer Kostenpost';
require 'header.php';

// --- ROLBEVEILIGING ---
if (!has_role('superuser')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bewerken.</div></section>";
    require 'footer.php';
    exit;
}

// Initialiseer variabelen
$kost = ['KostenID' => '', 'SchipID' => '', 'Datum' => date('Y-m-d'), 'Omschrijving' => '', 'Bedrag' => '', 'Type' => 'Overig'];
$jachtId = $_GET['jacht_id'] ?? null;
$kostId = $_GET['id'] ?? null;
$formAction = 'Toevoegen';
$errors = [];

// --- Slimme redirect-logica ---
$return_url = 'jachten_overzicht.php'; // Veilige fallback
if (isset($_REQUEST['return_url'])) {
    $return_url = filter_var($_REQUEST['return_url'], FILTER_SANITIZE_URL);
}

// Data ophalen voor wijzigen
if ($kostId) {
    $formAction = 'Wijzigen';
    $stmt = $db_connect->prepare("SELECT * FROM KostenLog WHERE KostenID = ?");
    $stmt->bind_param("i", $kostId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $kost = $result->fetch_assoc();
        $jachtId = $kost['SchipID'];
        if (empty($_REQUEST['return_url'])) {
            $return_url = "jachten.php?id=" . $jachtId . "#kosten";
        }
    }
} elseif ($jachtId) {
    $kost['SchipID'] = $jachtId;
    if (empty($_REQUEST['return_url'])) {
       $return_url = "jachten.php?id=" . $jachtId . "#kosten";
    }
}

// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kostId = $_POST['KostenID'] ?? null;
    $jachtId = (int)$_POST['SchipID'];
    $kost['Datum'] = $_POST['Datum'];
    $kost['Omschrijving'] = trim($_POST['Omschrijving']);
    $kost['Bedrag'] = (float)str_replace(',', '.', $_POST['Bedrag']);
    $kost['Type'] = $_POST['Type'];

    if (empty($kost['Omschrijving'])) $errors[] = "Omschrijving is verplicht.";
    if (empty($kost['Bedrag']) || $kost['Bedrag'] <= 0) $errors[] = "Voer een geldig bedrag in.";
    
    if (empty($errors)) {
        if ($kostId) { // UPDATE
            $stmt = $db_connect->prepare("UPDATE KostenLog SET Datum=?, Omschrijving=?, Bedrag=?, Type=? WHERE KostenID=?");
            $stmt->bind_param("ssdsi", $kost['Datum'], $kost['Omschrijving'], $kost['Bedrag'], $kost['Type'], $kostId);
        } else { // INSERT
            $stmt = $db_connect->prepare("INSERT INTO KostenLog (SchipID, Datum, Omschrijving, Bedrag, Type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isdss", $jachtId, $kost['Datum'], $kost['Omschrijving'], $kost['Bedrag'], $kost['Type']);
        }

        if ($stmt->execute()) {
            header("Location: " . $return_url);
            exit;
        } else {
            $errors[] = "Databasefout: " . $stmt->error;
        }
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Kostenpost <?php echo $formAction; ?></h2>
        <a href="<?php echo htmlspecialchars($return_url); ?>" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="kosten_form.php?id=<?php echo $kostId; ?>" method="post">
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">
                <input type="hidden" name="KostenID" value="<?php echo htmlspecialchars($kost['KostenID']); ?>">
                <input type="hidden" name="SchipID" value="<?php echo htmlspecialchars($kost['SchipID']); ?>">
                
                <fieldset>
                    <legend>Details Kostenpost</legend>
                    <div class="form-grid">
                        <div class="form-group"><label for="Datum">Datum</label><input type="date" id="Datum" name="Datum" value="<?php echo htmlspecialchars($kost['Datum']); ?>" required></div>
                        <div class="form-group"><label for="Bedrag">Bedrag (€)</label><input type="text" id="Bedrag" name="Bedrag" value="<?php echo htmlspecialchars($kost['Bedrag']); ?>" required></div>
                        <div class="form-group form-group-full"><label for="Omschrijving">Omschrijving</label><input type="text" id="Omschrijving" name="Omschrijving" value="<?php echo htmlspecialchars($kost['Omschrijving']); ?>" placeholder="bv. Advertentie YachtFocus, Keuringskosten" required></div>
                        <div class="form-group form-group-full"><label for="Type">Type Kosten</label>
                            <select id="Type" name="Type">
                                <option value="Reiniging" <?php echo ($kost['Type'] == 'Reiniging') ? 'selected' : ''; ?>>Reiniging</option>
                                <option value="Advertentie" <?php echo ($kost['Type'] == 'Advertentie') ? 'selected' : ''; ?>>Advertentie</option>
                                <option value="Keuring" <?php echo ($kost['Type'] == 'Keuring') ? 'selected' : ''; ?>>Keuring</option>
                                <option value="Onderhoud" <?php echo ($kost['Type'] == 'Onderhoud') ? 'selected' : ''; ?>>Onderhoud</option>
                                <option value="Overig" <?php echo ($kost['Type'] == 'Overig') ? 'selected' : ''; ?>>Overig</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
                
                <div class="form-group form-group-full">
                    <button type="submit" class="action-button-header">Opslaan</button>
                </div>
            </form>
        </div>
        <div class="form-sidebar">
            <div class="info-card">
                <h3><i class="fa-solid fa-file-invoice-dollar"></i> Kosten vastleggen</h3>
                <p>Hier kunt u losse kostenposten vastleggen die niet direct gekoppeld zijn aan een specifieke onderhoudsbeurt, zoals advertentiekosten of reiskosten.</p>
                <ul>
                    <li>Deze kosten worden opgeteld bij het <strong>"Totaal te verrekenen"</strong> op de kostenpagina van het jacht.</li>
                    <li>Kosten die voortkomen uit onderhoud, legt u vast in het <a href="onderhoud_form.php?jacht_id=<?php echo $jachtId; ?>">onderhoudsformulier</a>.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
