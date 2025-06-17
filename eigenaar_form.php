<?php
$pageTitle = 'Eigenaar Koppelen';
require 'header.php';

// Initialiseer variabelen
$jachtId = null;
$klantId = null;

$formAction = 'Koppelen';
$errors = [];

// --- GET-request verwerken om jacht/klant vooraf in te vullen ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['jacht_id'])) {
        $jachtId = (int)$_GET['jacht_id'];
    }
    if (isset($_GET['klant_id'])) {
        $klantId = (int)$_GET['klant_id'];
    }
}

// --- Haal alle jachten en klanten op voor de dropdowns ---
$result_jachten = $db_connect->query("SELECT SchipID, NaamSchip, MerkWerf FROM Schepen WHERE Status = 'Te Koop' OR Status = 'Onder Bod' ORDER BY NaamSchip");
$result_klanten = $db_connect->query("SELECT KlantID, Voornaam, Achternaam, Bedrijfsnaam, KlantType FROM Klanten ORDER BY Achternaam, Bedrijfsnaam");

// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jachtId = (int)$_POST['SchipID'];
    $klantId = (int)$_POST['KlantID'];
    $startDatum = $_POST['Startdatum'];

    // Validatie
    if (empty($jachtId)) $errors[] = "Selecteer een jacht.";
    if (empty($klantId)) $errors[] = "Selecteer een nieuwe eigenaar.";
    if (empty($startDatum)) $errors[] = "Selecteer een startdatum voor het eigenaarschap.";

    if (empty($errors)) {
        $db_connect->begin_transaction();
        try {
            // Stap 1: Zet alle 'Huidige Eigenaren' van dit schip op 'Ex-Eigenaar'
            $eindDatumVandaag = date('Y-m-d');
            $stmt_update_oud = $db_connect->prepare("UPDATE KlantSchipRelaties SET RelatieType = 'Ex-Eigenaar', Einddatum = ? WHERE SchipID = ? AND RelatieType = 'Huidige Eigenaar'");
            $stmt_update_oud->bind_param("si", $eindDatumVandaag, $jachtId);
            $stmt_update_oud->execute();

            // Stap 2: Voeg de nieuwe eigenaar toe
            $relatieType = 'Huidige Eigenaar';
            $stmt_insert_nieuw = $db_connect->prepare("INSERT INTO KlantSchipRelaties (KlantID, SchipID, RelatieType, Startdatum) VALUES (?, ?, ?, ?)");
            $stmt_insert_nieuw->bind_param("iiss", $klantId, $jachtId, $relatieType, $startDatum);
            $stmt_insert_nieuw->execute();
            
            // Stap 3: Update de status van het schip zelf naar 'Verkocht'
            $stmt_update_schip = $db_connect->prepare("UPDATE Schepen SET Status = 'Verkocht' WHERE SchipID = ?");
            $stmt_update_schip->bind_param("i", $jachtId);
            $stmt_update_schip->execute();

            $db_connect->commit();

            header("Location: jachten.php?id=" . $jachtId);
            exit;

        } catch (mysqli_sql_exception $exception) {
            $db_connect->rollback();
            $errors[] = "Databasefout tijdens transactie: " . $exception->getMessage();
        }
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Eigenaar Koppelen</h2>
        <a href="javascript:history.back()" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="eigenaar_form.php" method="post">
                <fieldset>
                    <legend>Nieuwe Eigenaar Toewijzen</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="SchipID">Jacht</label>
                            <select id="SchipID" name="SchipID" required <?php if ($jachtId) echo 'disabled'; ?>>
                                <option value="">-- Kies een jacht --</option>
                                <?php mysqli_data_seek($result_jachten, 0); while($jacht = $result_jachten->fetch_assoc()): ?>
                                    <option value="<?php echo $jacht['SchipID']; ?>" <?php echo ($jachtId == $jacht['SchipID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($jacht['NaamSchip'] . ' (' . $jacht['MerkWerf'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <?php if ($jachtId): ?>
                                <input type="hidden" name="SchipID" value="<?php echo htmlspecialchars($jachtId); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="KlantID">Nieuwe Eigenaar</label>
                            <select id="KlantID" name="KlantID" required <?php if ($klantId) echo 'disabled'; ?>>
                                <option value="">-- Kies een klant --</option>
                                <?php mysqli_data_seek($result_klanten, 0); while($klant = $result_klanten->fetch_assoc()): 
                                    $klantNaamForm = ($klant['KlantType'] == 'Bedrijf') ? $klant['Bedrijfsnaam'] : $klant['Voornaam'] . ' ' . $klant['Achternaam'];
                                ?>
                                    <option value="<?php echo $klant['KlantID']; ?>" <?php echo ($klantId == $klant['KlantID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($klantNaamForm); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <?php if ($klantId): ?>
                                <input type="hidden" name="KlantID" value="<?php echo htmlspecialchars($klantId); ?>">
                            <?php endif; ?>
                        </div>
                         <div class="form-group form-group-full">
                            <label for="Startdatum">Datum van overdracht</label>
                            <input type="date" id="Startdatum" name="Startdatum" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </fieldset>
                
                <div class="form-group form-group-full">
                    <button type="submit" class="action-button-header"><?php echo $formAction; ?> en verkoop afronden</button>
                </div>
            </form>
        </div>
        <div class="form-sidebar">
            <div class="info-card">
                <h3><i class="fa-solid fa-user-check"></i> Eigenaar Toewijzen</h3>
                <p>Met deze actie wordt de geselecteerde klant de nieuwe 'Huidige Eigenaar' van het jacht.</p>
                <ul>
                    <li>Eventuele vorige 'Huidige Eigenaren' worden automatisch omgezet naar 'Ex-Eigenaar'.</li>
                    <li>De status van het jacht wordt ingesteld op <strong>'Verkocht'</strong>.</li>
                </ul>
                <p>Deze actie is definitief en rondt het verkoopproces voor dit schip af in het systeem.</p>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
