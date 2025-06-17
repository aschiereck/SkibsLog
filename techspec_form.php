<?php
$pageTitle = 'Beheer Technische Specificatie';
require 'header.php';

// --- ROLBEVEILIGING ---
if (!has_role('user')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bewerken.</div></section>";
    require 'footer.php';
    exit;
}

// Initialiseer variabelen
$spec = ['SpecID' => '', 'SchipID' => '', 'Categorie' => 'Navigatie', 'Omschrijving' => '', 'MerkType' => ''];
$jachtId = $_GET['jacht_id'] ?? null;
$specId = $_GET['id'] ?? null;
$formAction = 'Toevoegen';

// Data ophalen voor wijzigen
if ($specId) {
    $formAction = 'Wijzigen';
    $stmt = $db_connect->prepare("SELECT * FROM TechnischeSpecificaties WHERE SpecID = ?");
    $stmt->bind_param("i", $specId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $spec = $result->fetch_assoc();
        $jachtId = $spec['SchipID'];
    }
} elseif ($jachtId) {
    $spec['SchipID'] = $jachtId;
}

// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jachtId = (int)$_POST['SchipID'];
    // ... (volledige POST-logica voor opslaan en updaten hier)
    header("Location: jachten.php?id=" . $jachtId . "#techniek");
    exit;
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Specificatie <?php echo $formAction; ?></h2>
        <a href="jachten.php?id=<?php echo $jachtId; ?>#techniek" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="techspec_form.php?id=<?php echo $specId; ?>" method="post">
                <input type="hidden" name="SpecID" value="<?php echo htmlspecialchars($spec['SpecID']); ?>">
                <input type="hidden" name="SchipID" value="<?php echo htmlspecialchars($spec['SchipID']); ?>">
                
                <fieldset>
                    <legend>Specificatie Details</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Categorie">Categorie</label>
                            <select id="Categorie" name="Categorie">
                                <option value="Navigatie" <?php echo ($spec['Categorie'] == 'Navigatie') ? 'selected' : ''; ?>>Navigatie</option>
                                <option value="Techniek" <?php echo ($spec['Categorie'] == 'Techniek') ? 'selected' : ''; ?>>Techniek</option>
                                <option value="Accommodatie" <?php echo ($spec['Categorie'] == 'Accommodatie') ? 'selected' : ''; ?>>Accommodatie</option>
                                <option value="Veiligheid" <?php echo ($spec['Categorie'] == 'Veiligheid') ? 'selected' : ''; ?>>Veiligheid</option>
                            </select>
                        </div>
                        <div class="form-group"><label for="Omschrijving">Omschrijving</label><input type="text" id="Omschrijving" name="Omschrijving" value="<?php echo htmlspecialchars($spec['Omschrijving']); ?>"></div>
                        <div class="form-group form-group-full"><label for="MerkType">Merk / Type</label><input type="text" id="MerkType" name="MerkType" value="<?php echo htmlspecialchars($spec['MerkType']); ?>"></div>
                    </div>
                </fieldset>
                
                <div class="form-actions-bar">
                     <button type="submit" class="action-button-header">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
