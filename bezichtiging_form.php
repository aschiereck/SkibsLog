<?php
$pageTitle = 'Beheer Bezichtiging';
require 'header.php';

// --- ROLBEVEILIGING ---
if (!has_role('user')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze pagina te bewerken.</div></section>";
    require 'footer.php';
    exit;
}

// Initialiseer variabelen
$bezichtiging = [
    'BezichtigingID' => '', 'SchipID' => '', 'KlantID' => '', 
    'Datum' => date('Y-m-d'), 'Tijd' => '12:00', 'Status' => 'Gepland', 
    'Begeleider' => $huidige_gebruiker, 'FeedbackKlant' => '', 'Vervolgactie' => ''
];
$formAction = 'Plannen';
$errors = [];

// --- GET-request verwerken om jacht/klant vooraf in te vullen ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['jacht_id'])) {
        $bezichtiging['SchipID'] = (int)$_GET['jacht_id'];
    }
    if (isset($_GET['klant_id'])) {
        $bezichtiging['KlantID'] = (int)$_GET['klant_id'];
    }
}

// --- Haal alle jachten en klanten op voor de dropdowns ---
$result_jachten = $db_connect->query("SELECT SchipID, NaamSchip, MerkWerf FROM Schepen WHERE Status = 'Te Koop' OR Status = 'Onder Bod' ORDER BY NaamSchip");
$result_klanten = $db_connect->query("SELECT KlantID, Voornaam, Achternaam, Bedrijfsnaam, KlantType FROM Klanten ORDER BY Achternaam, Bedrijfsnaam");

// --- POST-request verwerken (wanneer het formulier wordt ingediend) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_role('user')) { die('Ongeautoriseerde actie.'); }

    $bezichtigingId = $_POST['BezichtigingID'] ?? null;
    $bezichtiging['SchipID'] = (int)$_POST['SchipID'];
    $bezichtiging['KlantID'] = (int)$_POST['KlantID'];
    $bezichtiging['Datum'] = $_POST['Datum'];
    $bezichtiging['Tijd'] = $_POST['Tijd'];
    $bezichtiging['Status'] = $_POST['Status'];
    $bezichtiging['Begeleider'] = trim($_POST['Begeleider']);
    $bezichtiging['FeedbackKlant'] = trim($_POST['FeedbackKlant']);
    $bezichtiging['Vervolgactie'] = trim($_POST['Vervolgactie']);

    // Validatie
    if (empty($bezichtiging['SchipID'])) $errors[] = "Selecteer een jacht.";
    if (empty($bezichtiging['KlantID'])) $errors[] = "Selecteer een klant.";
    if (empty($bezichtiging['Datum'])) $errors[] = "Datum is verplicht.";

    if (empty($errors)) {
        if ($bezichtigingId) { // --- UPDATE ---
            $stmt = $db_connect->prepare("UPDATE Bezichtigingen SET SchipID=?, KlantID=?, Datum=?, Tijd=?, Status=?, Begeleider=?, FeedbackKlant=?, Vervolgactie=? WHERE BezichtigingID=?");
            $stmt->bind_param("iissssssi", $bezichtiging['SchipID'], $bezichtiging['KlantID'], $bezichtiging['Datum'], $bezichtiging['Tijd'], $bezichtiging['Status'], $bezichtiging['Begeleider'], $bezichtiging['FeedbackKlant'], $bezichtiging['Vervolgactie'], $bezichtigingId);
        } else { // --- INSERT ---
            $stmt = $db_connect->prepare("INSERT INTO Bezichtigingen (SchipID, KlantID, Datum, Tijd, Status, Begeleider, FeedbackKlant, Vervolgactie) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssss", $bezichtiging['SchipID'], $bezichtiging['KlantID'], $bezichtiging['Datum'], $bezichtiging['Tijd'], $bezichtiging['Status'], $bezichtiging['Begeleider'], $bezichtiging['FeedbackKlant'], $bezichtiging['Vervolgactie']);
        }

        if ($stmt->execute()) {
            header("Location: agenda.php"); // Stuur door naar het agenda-overzicht
            exit;
        } else {
            $errors[] = "Databasefout: " . $stmt->error;
        }
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) { // --- GET-request (data ophalen voor wijzigen) ---
    $formAction = 'Wijzigen';
    $bezichtigingId = (int)$_GET['id'];
    $stmt = $db_connect->prepare("SELECT * FROM Bezichtigingen WHERE BezichtigingID = ?");
    $stmt->bind_param("i", $bezichtigingId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $bezichtiging = $result->fetch_assoc();
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Bezichtiging <?php echo $formAction; ?></h2>
        <a href="agenda.php" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="bezichtiging_form.php?id=<?php echo $bezichtiging['BezichtigingID']; ?>" method="post">
                <input type="hidden" name="BezichtigingID" value="<?php echo htmlspecialchars($bezichtiging['BezichtigingID']); ?>">
                
                <fieldset>
                    <legend>Koppeling</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="SchipID">Selecteer Jacht</label>
                            <select id="SchipID" name="SchipID" required>
                                <option value="">-- Kies een jacht --</option>
                                <?php mysqli_data_seek($result_jachten, 0); while($jacht = $result_jachten->fetch_assoc()): ?>
                                    <option value="<?php echo $jacht['SchipID']; ?>" <?php echo ($bezichtiging['SchipID'] == $jacht['SchipID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($jacht['NaamSchip'] . ' (' . $jacht['MerkWerf'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="KlantID">Selecteer Klant</label>
                            <select id="KlantID" name="KlantID" required>
                                <option value="">-- Kies een klant --</option>
                                <?php mysqli_data_seek($result_klanten, 0); while($klant = $result_klanten->fetch_assoc()): 
                                    $klantNaam = ($klant['KlantType'] == 'Bedrijf') ? $klant['Bedrijfsnaam'] : $klant['Voornaam'] . ' ' . $klant['Achternaam'];
                                ?>
                                    <option value="<?php echo $klant['KlantID']; ?>" <?php echo ($bezichtiging['KlantID'] == $klant['KlantID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($klantNaam); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Afspraak Details</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Datum">Datum</label>
                            <input type="date" id="Datum" name="Datum" value="<?php echo htmlspecialchars($bezichtiging['Datum']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="Tijd">Tijd</label>
                            <input type="time" id="Tijd" name="Tijd" value="<?php echo htmlspecialchars($bezichtiging['Tijd']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="Begeleider">Begeleider</label>
                            <input type="text" id="Begeleider" name="Begeleider" value="<?php echo htmlspecialchars($bezichtiging['Begeleider']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="Status">Status</label>
                            <select id="Status" name="Status">
                                <option value="Gepland" <?php echo ($bezichtiging['Status'] == 'Gepland') ? 'selected' : ''; ?>>Gepland</option>
                                <option value="Afgerond" <?php echo ($bezichtiging['Status'] == 'Afgerond') ? 'selected' : ''; ?>>Afgerond</option>
                                <option value="Geannuleerd" <?php echo ($bezichtiging['Status'] == 'Geannuleerd') ? 'selected' : ''; ?>>Geannuleerd</option>
                                <option value="No-show" <?php echo ($bezichtiging['Status'] == 'No-show') ? 'selected' : ''; ?>>No-show</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Follow-up</legend>
                     <div class="form-group form-group-full">
                        <label for="FeedbackKlant">Feedback van Klant</label>
                        <textarea id="FeedbackKlant" name="FeedbackKlant" rows="4"><?php echo htmlspecialchars($bezichtiging['FeedbackKlant']); ?></textarea>
                    </div>
                     <div class="form-group form-group-full">
                        <label for="Vervolgactie">Interne Vervolgactie</label>
                        <textarea id="Vervolgactie" name="Vervolgactie" rows="2"><?php echo htmlspecialchars($bezichtiging['Vervolgactie']); ?></textarea>
                    </div>
                </fieldset>

                <div class="form-group form-group-full">
                    <button type="submit" class="action-button-header" <?php if (!has_role('user')) echo 'disabled'; ?>>
                        <?php echo $formAction; ?> Bezichtiging
                    </button>
                </div>
            </form>
        </div>
        <div class="form-sidebar">
            <div class="info-card">
                <h3><i class="fa-solid fa-circle-info"></i> Bezichtiging Inplannen</h3>
                <p>Een bezichtiging is de eerste stap naar een succesvolle verkoop. Zorg ervoor dat alle gegevens correct zijn ingevuld.</p>
                <ul>
                    <li>Koppel de juiste <strong>klant</strong> aan het juiste <strong>jacht</strong>.</li>
                    <li>Controleer de datum en tijd dubbel om misverstanden te voorkomen.</li>
                    <li>De begeleider is standaard de ingelogde gebruiker.</li>
                </ul>
                <p>Na afloop van de bezichtiging kunt u hier de status aanpassen en de feedback van de klant noteren.</p>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
