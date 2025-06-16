<?php
$pageTitle = 'Beheer Motorjacht';
require 'header.php';

$jacht = [
    'SchipID' => '', 'Status' => 'Te Koop', 'NaamSchip' => '', 'MerkWerf' => '',
    'ModelType' => '', 'Bouwjaar' => date('Y'), 'Vraagprijs' => '', 'Ligplaats' => '',
    'BTWStatus' => 'Betaald', 'Lengte' => '', 'Breedte' => '', 'Diepgang' => '',
    'OmschrijvingAlg' => ''
];
$formAction = 'Toevoegen';
$errors = [];

// --- POST-request verwerken (wanneer het formulier wordt ingediend) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Data uit het formulier halen en opschonen
    $jachtId = $_POST['SchipID'] ?? null;
    $jacht['Status'] = $_POST['Status'];
    $jacht['NaamSchip'] = trim($_POST['NaamSchip']);
    $jacht['MerkWerf'] = trim($_POST['MerkWerf']);
    $jacht['ModelType'] = trim($_POST['ModelType']);
    $jacht['Bouwjaar'] = (int)$_POST['Bouwjaar'];
    $jacht['Vraagprijs'] = (float)str_replace(',', '.', $_POST['Vraagprijs']);
    $jacht['Ligplaats'] = trim($_POST['Ligplaats']);
    $jacht['BTWStatus'] = $_POST['BTWStatus'];
    $jacht['Lengte'] = (float)str_replace(',', '.', $_POST['Lengte']);
    $jacht['Breedte'] = (float)str_replace(',', '.', $_POST['Breedte']);
    $jacht['Diepgang'] = (float)str_replace(',', '.', $_POST['Diepgang']);
    $jacht['OmschrijvingAlg'] = trim($_POST['OmschrijvingAlg']);

    // Validatie
    if (empty($jacht['NaamSchip'])) $errors[] = "Naam schip is verplicht.";
    if (empty($jacht['MerkWerf'])) $errors[] = "Merk/Werf is verplicht.";
    if ($jacht['Bouwjaar'] < 1900 || $jacht['Bouwjaar'] > date('Y') + 1) $errors[] = "Ongeldig bouwjaar.";

    if (empty($errors)) {
        if ($jachtId) { // --- UPDATE (Stap 3) ---
            $stmt = $db_connect->prepare("UPDATE Schepen SET Status=?, NaamSchip=?, MerkWerf=?, ModelType=?, Bouwjaar=?, Vraagprijs=?, Ligplaats=?, BTWStatus=?, Lengte=?, Breedte=?, Diepgang=?, OmschrijvingAlg=? WHERE SchipID=?");
            $stmt->bind_param("ssssidssdddsi", $jacht['Status'], $jacht['NaamSchip'], $jacht['MerkWerf'], $jacht['ModelType'], $jacht['Bouwjaar'], $jacht['Vraagprijs'], $jacht['Ligplaats'], $jacht['BTWStatus'], $jacht['Lengte'], $jacht['Breedte'], $jacht['Diepgang'], $jacht['OmschrijvingAlg'], $jachtId);
            $success = $stmt->execute();
        } else { // --- INSERT (Stap 2) ---
            $stmt = $db_connect->prepare("INSERT INTO Schepen (Status, NaamSchip, MerkWerf, ModelType, Bouwjaar, Vraagprijs, Ligplaats, BTWStatus, Lengte, Breedte, Diepgang, OmschrijvingAlg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssidssddds", $jacht['Status'], $jacht['NaamSchip'], $jacht['MerkWerf'], $jacht['ModelType'], $jacht['Bouwjaar'], $jacht['Vraagprijs'], $jacht['Ligplaats'], $jacht['BTWStatus'], $jacht['Lengte'], $jacht['Breedte'], $jacht['Diepgang'], $jacht['OmschrijvingAlg']);
            $success = $stmt->execute();
            $jachtId = $db_connect->insert_id;
        }

        if ($success) {
            header("Location: jacht_detail.php?id=" . $jachtId); // Stuur door naar detailpagina
            exit;
        } else {
            $errors[] = "Er is een databasefout opgetreden: " . $stmt->error;
        }
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) { // --- GET-request (data ophalen voor wijzigen) ---
    $formAction = 'Wijzigen';
    $jachtId = (int)$_GET['id'];
    $stmt = $db_connect->prepare("SELECT * FROM Schepen WHERE SchipID = ?");
    $stmt->bind_param("i", $jachtId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $jacht = $result->fetch_assoc();
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Jacht <?php echo $formAction; ?></h2>
        <a href="jachten.php" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="jacht_form.php" method="post" class="form-grid">
        <input type="hidden" name="SchipID" value="<?php echo htmlspecialchars($jacht['SchipID']); ?>">

        <div class="form-group">
            <label for="NaamSchip">Naam Schip</label>
            <input type="text" id="NaamSchip" name="NaamSchip" value="<?php echo htmlspecialchars($jacht['NaamSchip']); ?>" required>
        </div>
        <div class="form-group">
            <label for="Status">Status</label>
            <select id="Status" name="Status">
                <option value="Te Koop" <?php echo ($jacht['Status'] == 'Te Koop') ? 'selected' : ''; ?>>Te Koop</option>
                <option value="Onder Bod" <?php echo ($jacht['Status'] == 'Onder Bod') ? 'selected' : ''; ?>>Onder Bod</option>
                <option value="Verkocht" <?php echo ($jacht['Status'] == 'Verkocht') ? 'selected' : ''; ?>>Verkocht</option>
                <option value="In portefeuille" <?php echo ($jacht['Status'] == 'In portefeuille') ? 'selected' : ''; ?>>In portefeuille</option>
            </select>
        </div>
        <div class="form-group">
            <label for="MerkWerf">Merk / Werf</label>
            <input type="text" id="MerkWerf" name="MerkWerf" value="<?php echo htmlspecialchars($jacht['MerkWerf']); ?>" required>
        </div>
        <div class="form-group">
            <label for="ModelType">Model / Type</label>
            <input type="text" id="ModelType" name="ModelType" value="<?php echo htmlspecialchars($jacht['ModelType']); ?>" required>
        </div>
        <div class="form-group">
            <label for="Bouwjaar">Bouwjaar</label>
            <input type="number" id="Bouwjaar" name="Bouwjaar" value="<?php echo htmlspecialchars($jacht['Bouwjaar']); ?>" required>
        </div>
        <div class="form-group">
            <label for="Vraagprijs">Vraagprijs</label>
            <input type="text" id="Vraagprijs" name="Vraagprijs" value="<?php echo htmlspecialchars($jacht['Vraagprijs']); ?>">
        </div>
        <div class="form-group">
            <label for="Ligplaats">Ligplaats</label>
            <input type="text" id="Ligplaats" name="Ligplaats" value="<?php echo htmlspecialchars($jacht['Ligplaats']); ?>">
        </div>
        <div class="form-group">
            <label for="BTWStatus">BTW Status</label>
             <select id="BTWStatus" name="BTWStatus">
                <option value="Betaald" <?php echo ($jacht['BTWStatus'] == 'Betaald') ? 'selected' : ''; ?>>Betaald</option>
                <option value="Niet betaald" <?php echo ($jacht['BTWStatus'] == 'Niet betaald') ? 'selected' : ''; ?>>Niet betaald</option>
                <option value="Verrekenbaar" <?php echo ($jacht['BTWStatus'] == 'Verrekenbaar') ? 'selected' : ''; ?>>Verrekenbaar</option>
            </select>
        </div>
        <div class="form-group">
            <label for="Lengte">Lengte (m)</label>
            <input type="text" id="Lengte" name="Lengte" value="<?php echo htmlspecialchars($jacht['Lengte']); ?>">
        </div>
         <div class="form-group">
            <label for="Breedte">Breedte (m)</label>
            <input type="text" id="Breedte" name="Breedte" value="<?php echo htmlspecialchars($jacht['Breedte']); ?>">
        </div>
         <div class="form-group">
            <label for="Diepgang">Diepgang (m)</label>
            <input type="text" id="Diepgang" name="Diepgang" value="<?php echo htmlspecialchars($jacht['Diepgang']); ?>">
        </div>
        <div class="form-group form-group-full">
            <label for="OmschrijvingAlg">Omschrijving</label>
            <textarea id="OmschrijvingAlg" name="OmschrijvingAlg" rows="6"><?php echo htmlspecialchars($jacht['OmschrijvingAlg']); ?></textarea>
        </div>
        <div class="form-group form-group-full">
            <button type="submit" class="action-button-header"><?php echo $formAction; ?> Jacht</button>
        </div>
    </form>
</section>

<?php require 'footer.php'; ?>
