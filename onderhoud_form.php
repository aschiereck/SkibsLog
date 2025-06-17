<?php
$pageTitle = 'Beheer Onderhoudslog';
require 'header.php';

// --- ROLBEVEILIGING ---
if (!has_role('user')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bewerken.</div></section>";
    require 'footer.php';
    exit;
}

// Initialiseer variabelen
$log = ['OnderhoudsID' => '', 'SchipID' => '', 'Datum' => date('Y-m-d'), 'TypeGebeurtenis' => '', 'Omschrijving' => '', 'UitgevoerdDoor' => '', 'Bedrag' => '', 'StatusBetaling' => 'Te Verrekenen'];
$jachtId = $_GET['jacht_id'] ?? null;
$logId = $_GET['id'] ?? null;
$formAction = 'Toevoegen';
$errors = [];

// --- NIEUW: Slimme redirect-logica ---
// Bepaal de URL om naar terug te keren na opslaan of annuleren
$return_url = 'jachten_overzicht.php'; // Veilige standaard fallback
if (isset($_REQUEST['return_url'])) {
    // Verwijder potentieel gevaarlijke tekens uit de URL
    $return_url = filter_var($_REQUEST['return_url'], FILTER_SANITIZE_URL);
}

// Data ophalen voor wijzigen
if ($logId) {
    $formAction = 'Wijzigen';
    $stmt = $db_connect->prepare("SELECT * FROM OnderhoudsLog WHERE OnderhoudsID = ?");
    $stmt->bind_param("i", $logId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $log = $result->fetch_assoc();
        $jachtId = $log['SchipID'];
        // Als er geen return_url is meegegeven, maak een standaard-URL
        if (empty($_REQUEST['return_url'])) {
            $return_url = "jachten.php?id=" . $jachtId . "#onderhoud";
        }
    }
} elseif ($jachtId) {
    $log['SchipID'] = $jachtId;
     if (empty($_REQUEST['return_url'])) {
        $return_url = "jachten.php?id=" . $jachtId . "#onderhoud";
    }
}


// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logId = $_POST['OnderhoudsID'] ?? null;
    $jachtId = (int)$_POST['SchipID'];
    $log['Datum'] = $_POST['Datum'];
    $log['TypeGebeurtenis'] = trim($_POST['TypeGebeurtenis']);
    $log['Omschrijving'] = trim($_POST['Omschrijving']);
    $log['UitgevoerdDoor'] = trim($_POST['UitgevoerdDoor']);
    $log['Bedrag'] = !empty($_POST['Bedrag']) ? (float)str_replace(',', '.', $_POST['Bedrag']) : null;
    $log['StatusBetaling'] = !empty($log['Bedrag']) ? $_POST['StatusBetaling'] : null;

    if (empty($log['TypeGebeurtenis'])) $errors[] = "Type gebeurtenis is verplicht.";
    
    if (empty($errors)) {
        if ($logId) { // UPDATE
            $stmt = $db_connect->prepare("UPDATE OnderhoudsLog SET Datum=?, TypeGebeurtenis=?, Omschrijving=?, UitgevoerdDoor=?, Bedrag=?, StatusBetaling=? WHERE OnderhoudsID=?");
            $stmt->bind_param("ssssdsi", $log['Datum'], $log['TypeGebeurtenis'], $log['Omschrijving'], $log['UitgevoerdDoor'], $log['Bedrag'], $log['StatusBetaling'], $logId);
        } else { // INSERT
            $stmt = $db_connect->prepare("INSERT INTO OnderhoudsLog (SchipID, Datum, TypeGebeurtenis, Omschrijving, UitgevoerdDoor, Bedrag, StatusBetaling) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssds", $jachtId, $log['Datum'], $log['TypeGebeurtenis'], $log['Omschrijving'], $log['UitgevoerdDoor'], $log['Bedrag'], $log['StatusBetaling']);
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
        <h2>Onderhoudslog <?php echo $formAction; ?></h2>
        <a href="<?php echo htmlspecialchars($return_url); ?>" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="onderhoud_form.php?id=<?php echo $logId; ?>" method="post">
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">
                <input type="hidden" name="OnderhoudsID" value="<?php echo htmlspecialchars($log['OnderhoudsID']); ?>">
                <input type="hidden" name="SchipID" value="<?php echo htmlspecialchars($log['SchipID']); ?>">
                
                <fieldset>
                    <legend>Onderhoudsdetails</legend>
                    <div class="form-grid">
                        <div class="form-group"><label for="Datum">Datum</label><input type="date" id="Datum" name="Datum" value="<?php echo htmlspecialchars($log['Datum']); ?>" required></div>
                        <div class="form-group"><label for="TypeGebeurtenis">Type</label><input type="text" id="TypeGebeurtenis" name="TypeGebeurtenis" value="<?php echo htmlspecialchars($log['TypeGebeurtenis']); ?>" placeholder="bv. Motorbeurt, Reiniging" required></div>
                        <div class="form-group form-group-full"><label for="Omschrijving">Omschrijving</label><textarea id="Omschrijving" name="Omschrijving" rows="4"><?php echo htmlspecialchars($log['Omschrijving']); ?></textarea></div>
                        <div class="form-group form-group-full"><label for="UitgevoerdDoor">Uitgevoerd door</label><input type="text" id="UitgevoerdDoor" name="UitgevoerdDoor" value="<?php echo htmlspecialchars($log['UitgevoerdDoor']); ?>"></div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Financiële Afhandeling (Optioneel)</legend>
                    <div class="form-grid">
                         <div class="form-group"><label for="Bedrag">Kosten (€)</label><input type="text" id="Bedrag" name="Bedrag" value="<?php echo htmlspecialchars($log['Bedrag']); ?>"></div>
                         <div class="form-group"><label for="StatusBetaling">Betaalstatus</label>
                            <select id="StatusBetaling" name="StatusBetaling">
                                <option value="Te Verrekenen" <?php echo ($log['StatusBetaling'] == 'Te Verrekenen') ? 'selected' : ''; ?>>Te Verrekenen via SkibsLog</option>
                                <option value="Betaald door Eigenaar" <?php echo ($log['StatusBetaling'] == 'Betaald door Eigenaar') ? 'selected' : ''; ?>>Reeds betaald door eigenaar</option>
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
