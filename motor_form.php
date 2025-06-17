<?php
$pageTitle = 'Beheer Motor';
require 'header.php';

// --- ROLBEVEILIGING ---
if (!has_role('user')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bewerken.</div></section>";
    require 'footer.php';
    exit;
}

// Initialiseer variabelen
$motor = ['MotorID' => '', 'SchipID' => '', 'Merk' => '', 'Type' => '', 'Vermogen' => '', 'Draaiuren' => ''];
$jachtId = $_GET['jacht_id'] ?? null;
$motorId = $_GET['id'] ?? null;
$formAction = 'Toevoegen';
$errors = [];

// Data ophalen voor wijzigen
if ($motorId) {
    $formAction = 'Wijzigen';
    $stmt = $db_connect->prepare("SELECT * FROM Motoren WHERE MotorID = ?");
    $stmt->bind_param("i", $motorId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $motor = $result->fetch_assoc();
        $jachtId = $motor['SchipID']; // Haal jacht ID op van de te bewerken motor
    }
} elseif ($jachtId) {
    $motor['SchipID'] = $jachtId;
}

// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Acties zoals Verwijderen
    if (isset($_POST['actie']) && $_POST['actie'] == 'verwijder') {
        if ($motorId && has_role('superuser')) {
            $stmt = $db_connect->prepare("DELETE FROM Motoren WHERE MotorID = ?");
            $stmt->bind_param("i", $motorId);
            $stmt->execute();
        }
        header("Location: jachten.php?id=" . $_POST['SchipID']);
        exit;
    }

    // Data uit formulier halen
    $motorId = $_POST['MotorID'] ?? null;
    $jachtId = (int)$_POST['SchipID'];
    $motorData = [
        'Merk' => trim($_POST['Merk']),
        'Type' => trim($_POST['Type']),
        'Vermogen' => trim($_POST['Vermogen']),
        'Draaiuren' => (int)$_POST['Draaiuren']
    ];

    if ($motorId) { // UPDATE
        $stmt = $db_connect->prepare("UPDATE Motoren SET Merk=?, Type=?, Vermogen=?, Draaiuren=? WHERE MotorID=?");
        $stmt->bind_param("sssii", $motorData['Merk'], $motorData['Type'], $motorData['Vermogen'], $motorData['Draaiuren'], $motorId);
        $stmt->execute();
    } else { // INSERT
        $stmt = $db_connect->prepare("INSERT INTO Motoren (SchipID, Merk, Type, Vermogen, Draaiuren) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $jachtId, $motorData['Merk'], $motorData['Type'], $motorData['Vermogen'], $motorData['Draaiuren']);
        $stmt->execute();
    }

    // Check voor 'Opslaan & Verdubbelen'
    if (isset($_POST['save_and_duplicate'])) {
        $motorId = null; // Zorg dat de volgende save een INSERT is
        // Herlaad de pagina met dezelfde data, maar zonder motorID
        $_GET['jacht_id'] = $jachtId; // Zorg dat de jacht ID behouden blijft
        $motor = ['SchipID' => $jachtId] + $motorData;
        $formAction = 'Toevoegen (Duplicaat)';
    } else {
        header("Location: jachten.php?id=" . $jachtId);
        exit;
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Motor <?php echo $formAction; ?></h2>
        <a href="jachten.php?id=<?php echo $jachtId; ?>" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="motor_form.php?id=<?php echo $motorId; ?>" method="post">
                <input type="hidden" name="MotorID" value="<?php echo htmlspecialchars($motor['MotorID']); ?>">
                <input type="hidden" name="SchipID" value="<?php echo htmlspecialchars($motor['SchipID']); ?>">
                
                <fieldset>
                    <legend>Motor Specificaties</legend>
                    <div class="form-grid">
                        <div class="form-group"><label for="Merk">Merk</label><input type="text" id="Merk" name="Merk" value="<?php echo htmlspecialchars($motor['Merk']); ?>"></div>
                        <div class="form-group"><label for="Type">Type</label><input type="text" id="Type" name="Type" value="<?php echo htmlspecialchars($motor['Type']); ?>"></div>
                        <div class="form-group"><label for="Vermogen">Vermogen</label><input type="text" id="Vermogen" name="Vermogen" value="<?php echo htmlspecialchars($motor['Vermogen']); ?>"></div>
                        <div class="form-group"><label for="Draaiuren">Draaiuren</label><input type="number" id="Draaiuren" name="Draaiuren" value="<?php echo htmlspecialchars($motor['Draaiuren']); ?>"></div>
                    </div>
                </fieldset>
                
                <div class="form-actions-bar">
                    <div>
                        <button type="submit" class="action-button-header">Opslaan</button>
                        <?php if (empty($motorId)): // Toon alleen bij nieuwe motor ?>
                        <button type="submit" name="save_and_duplicate" class="action-button-header-secondary">Opslaan & Verdubbelen</button>
                        <?php endif; ?>
                    </div>
                    <?php if ($motorId && has_role('superuser')): ?>
                    <button type="submit" name="actie" value="verwijder" class="form-delete-button" onclick="return confirm('Weet u zeker dat u deze motor wilt verwijderen?');"><i class="fa-solid fa-trash"></i></button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
