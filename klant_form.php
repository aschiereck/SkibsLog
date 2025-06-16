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
    
    // Validatie
    if ($klant['KlantType'] == 'Bedrijf' && empty($klant['Bedrijfsnaam'])) {
        $errors[] = "Bedrijfsnaam is verplicht voor type 'Bedrijf'.";
    }
    if ($klant['KlantType'] != 'Bedrijf' && empty($klant['Achternaam'])) {
        $errors[] = "Achternaam is verplicht.";
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
            if (!$klantId) $klantId = $db_connect->insert_id;
            header("Location: klant_detail.php?id=" . $klantId);
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
        <a href="klanten.php" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

     <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form action="klant_form.php" method="post" class="form-grid">
        <input type="hidden" name="KlantID" value="<?php echo htmlspecialchars($klant['KlantID']); ?>">
        
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
            <input type="text" id="Achternaam" name="Achternaam" value="<?php echo htmlspecialchars($klant['Achternaam']); ?>" required>
        </div>
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
        <div class="form-group">
            <label for="Telefoonnummer1">Telefoonnummer</label>
            <input type="tel" id="Telefoonnummer1" name="Telefoonnummer1" value="<?php echo htmlspecialchars($klant['Telefoonnummer1']); ?>">
        </div>
        <div class="form-group form-group-full">
            <label for="Emailadres">E-mailadres</label>
            <input type="email" id="Emailadres" name="Emailadres" value="<?php echo htmlspecialchars($klant['Emailadres']); ?>">
        </div>
        <div class="form-group form-group-full">
            <label for="Notities">Notities</label>
            <textarea id="Notities" name="Notities" rows="4"><?php echo htmlspecialchars($klant['Notities']); ?></textarea>
        </div>
        <div class="form-group form-group-full">
            <button type="submit" class="action-button-header"><?php echo $formAction; ?> Klant</button>
        </div>
    </form>
</section>

<?php require 'footer.php'; ?>
