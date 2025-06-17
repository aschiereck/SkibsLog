<?php
$pageTitle = 'Beheer Klant';
require 'header.php';

$klant = [
    'KlantID' => '', 'KlantType' => 'Persoon', 'Voornaam' => '', 'Achternaam' => '',
    'Bedrijfsnaam' => '', 'Adres' => '', 'Postcode' => '', 'Woonplaats' => '',
    'Land' => 'Nederland', 'Telefoonnummer1' => '', 'Emailadres' => '', 'Notities' => ''
];
$formAction = 'Toevoegen';
$errors = [];

// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $klantId = $_POST['KlantID'] ?? null;
    $klant['KlantType'] = $_POST['KlantType'];
    $klant['Voornaam'] = trim($_POST['Voornaam']);
    $klant['Achternaam'] = trim($_POST['Achternaam']);
    $klant['Bedrijfsnaam'] = trim($_POST['Bedrijfsnaam']);
    $klant['Adres'] = trim($_POST['Adres']);
    $klant['Postcode'] = trim($_POST['Postcode']);
    $klant['Woonplaats'] = trim($_POST['Woonplaats']);
    $klant['Land'] = trim($_POST['Land']);
    $klant['Telefoonnummer1'] = trim($_POST['Telefoonnummer1']);
    $klant['Emailadres'] = trim($_POST['Emailadres']);
    $klant['Notities'] = trim($_POST['Notities']);
    
    // --- GECORRIGEERDE VALIDATIE ---
    if ($klant['KlantType'] == 'Bedrijf') {
        if (empty($klant['Bedrijfsnaam'])) {
            $errors[] = "Bedrijfsnaam is verplicht voor het klanttype 'Bedrijf'.";
        }
    } else { // Voor 'Persoon' of 'Echtpaar/Familie'
        if (empty($klant['Achternaam'])) {
            $errors[] = "Achternaam is verplicht voor de klanttypes 'Persoon' en 'Echtpaar/Familie'.";
        }
    }

    if (empty($errors)) {
        if ($klantId) { // --- UPDATE ---
            $stmt = $db_connect->prepare("UPDATE Klanten SET KlantType=?, Voornaam=?, Achternaam=?, Bedrijfsnaam=?, Adres=?, Postcode=?, Woonplaats=?, Land=?, Telefoonnummer1=?, Emailadres=?, Notities=? WHERE KlantID=?");
            $stmt->bind_param("sssssssssssi", $klant['KlantType'], $klant['Voornaam'], $klant['Achternaam'], $klant['Bedrijfsnaam'], $klant['Adres'], $klant['Postcode'], $klant['Woonplaats'], $klant['Land'], $klant['Telefoonnummer1'], $klant['Emailadres'], $klant['Notities'], $klantId);
        } else { // --- INSERT ---
            $stmt = $db_connect->prepare("INSERT INTO Klanten (KlantType, Voornaam, Achternaam, Bedrijfsnaam, Adres, Postcode, Woonplaats, Land, Telefoonnummer1, Emailadres, Notities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssss", $klant['KlantType'], $klant['Voornaam'], $klant['Achternaam'], $klant['Bedrijfsnaam'], $klant['Adres'], $klant['Postcode'], $klant['Woonplaats'], $klant['Land'], $klant['Telefoonnummer1'], $klant['Emailadres'], $klant['Notities']);
        }

        if ($stmt->execute()) {
            // --- GECORRIGEERDE REDIRECT ---
            // Na het opslaan gaan we terug naar het overzicht, niet de detailpagina.
            header("Location: klanten_overzicht.php"); 
            exit;
        } else {
            $errors[] = "Databasefout: " . $stmt->error;
        }
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) { // --- GET-request (data ophalen) ---
    $formAction = 'Wijzigen';
    $klantId = (int)$_GET['id'];
    $stmt = $db_connect->prepare("SELECT * FROM Klanten WHERE KlantID = ?");
    $stmt->bind_param("i", $klantId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $klant = $result->fetch_assoc();
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Klant <?php echo $formAction; ?></h2>
        <a href="klanten_overzicht.php" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

     <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="klant_form.php" method="post" class="form-grid">
                <input type="hidden" name="KlantID" value="<?php echo htmlspecialchars($klant['KlantID']); ?>">
                
                <fieldset>
                    <legend>Persoons- / Bedrijfsgegevens</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="KlantType">Klanttype</label>
                            <select id="KlantType" name="KlantType">
                                <option value="Persoon" <?php echo ($klant['KlantType'] == 'Persoon') ? 'selected' : ''; ?>>Persoon</option>
                                <option value="Echtpaar/Familie" <?php echo ($klant['KlantType'] == 'Echtpaar/Familie') ? 'selected' : ''; ?>>Echtpaar/Familie</option>
                                <option value="Bedrijf" <?php echo ($klant['KlantType'] == 'Bedrijf') ? 'selected' : ''; ?>>Bedrijf</option>
                            </select>
                        </div>
                         <div class="form-group">
                            <label for="Bedrijfsnaam">Bedrijfsnaam</label>
                            <input type="text" id="Bedrijfsnaam" name="Bedrijfsnaam" value="<?php echo htmlspecialchars($klant['Bedrijfsnaam']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="Voornaam">Voornaam</label>
                            <input type="text" id="Voornaam" name="Voornaam" value="<?php echo htmlspecialchars($klant['Voornaam']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="Achternaam">Achternaam</label>
                            <input type="text" id="Achternaam" name="Achternaam" value="<?php echo htmlspecialchars($klant['Achternaam']); ?>">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Adresgegevens</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Adres">Adres</label>
                            <input type="text" id="Adres" name="Adres" value="<?php echo htmlspecialchars($klant['Adres']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="Postcode">Postcode</label>
                            <input type="text" id="Postcode" name="Postcode" value="<?php echo htmlspecialchars($klant['Postcode']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="Woonplaats">Woonplaats</label>
                            <input type="text" id="Woonplaats" name="Woonplaats" value="<?php echo htmlspecialchars($klant['Woonplaats']); ?>">
                        </div>
                        <!-- TOEGEVOEGD VELD -->
                        <div class="form-group">
                            <label for="Land">Land</label>
                            <input type="text" id="Land" name="Land" value="<?php echo htmlspecialchars($klant['Land']); ?>">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Contact</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Telefoonnummer1">Telefoonnummer</label>
                            <input type="tel" id="Telefoonnummer1" name="Telefoonnummer1" value="<?php echo htmlspecialchars($klant['Telefoonnummer1']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="Emailadres">E-mailadres</label>
                            <input type="email" id="Emailadres" name="Emailadres" value="<?php echo htmlspecialchars($klant['Emailadres']); ?>">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Overig</legend>
                    <div class="form-group form-group-full">
                        <label for="Notities">Notities</label>
                        <textarea id="Notities" name="Notities" rows="4"><?php echo htmlspecialchars($klant['Notities']); ?></textarea>
                    </div>
                </fieldset>
                
                <div class="form-group form-group-full">
                    <button type="submit" class="action-button-header"><?php echo $formAction; ?> Klant</button>
                </div>
            </form>
        </div>
        <div class="form-sidebar">
            <div class="info-card">
                <h3><i class="fa-solid fa-user-plus"></i> Klant beheren</h3>
                <p>Vul hier de gegevens in van een nieuwe of bestaande klant. Zorg ervoor dat de contactgegevens actueel zijn.</p>
                <ul>
                    <li>Bij een <strong>Bedrijf</strong> is de bedrijfsnaam leidend.</li>
                    <li>Bij een <strong>Persoon</strong> is de achternaam verplicht.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
