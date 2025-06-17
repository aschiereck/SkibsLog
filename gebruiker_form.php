<?php
$pageTitle = 'Beheer Gebruiker';
require 'header.php';

// --- ROLBEVEILIGING: Bepaal wie deze pagina mag zien en bewerken ---
$targetUserId = $_GET['id'] ?? $_SESSION['user_id']; // Gebruik ID uit URL of eigen ID
$isSelfEdit = ($targetUserId == $_SESSION['user_id']);

// Een 'user' mag alleen zijn eigen pagina bewerken.
// Een 'viewer' mag zijn eigen pagina bekijken maar niet opslaan.
// Superuser/admin mag (bijna) alles.
if (!$isSelfEdit && !has_role('superuser')) {
    echo "<section class='content-page'><div class='error-box'>U heeft geen rechten om deze gebruiker te bewerken.</div></section>";
    require 'footer.php';
    exit;
}


// --- Initialisatie en bestaande gebruiker ophalen ---
$user = [
    'UserID' => '', 'Gebruikersnaam' => '', 'VolledigeNaam' => '', 'Profielfoto' => null,
    'Rol' => 'viewer', 'IsActief' => 1
];
$formAction = 'Toevoegen';
$errors = [];


if ($targetUserId) {
    // Bewerk een bestaande gebruiker
    $formAction = 'Wijzigen';
    $stmt = $db_connect->prepare("SELECT * FROM Gebruikers WHERE UserID = ?");
    $stmt->bind_param("i", $targetUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        // Gebruiker niet gevonden, toon fout
        echo "<section class='content-page'><div class='error-box'>Gebruiker niet gevonden.</div></section>";
        require 'footer.php';
        exit;
    }
}


// --- POST-request verwerken ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userIdToUpdate = $_POST['UserID'] ?? null;
    $gebruikersnaam = trim($_POST['Gebruikersnaam']);
    $volledigeNaam = trim($_POST['VolledigeNaam']);
    $rol = $_POST['Rol'] ?? $user['Rol']; // Behoud oude rol als niet aanpasbaar
    $isActief = isset($_POST['IsActief']) ? 1 : 0;
    $wachtwoord = $_POST['wachtwoord'];
    $wachtwoord_herhaal = $_POST['wachtwoord_herhaal'];
    
    // Security check: mag deze gebruiker de update uitvoeren?
    $canEditDetails = ($isSelfEdit && has_role('user')) || has_role('superuser');
    if (!$canEditDetails) {
        die('Ongeautoriseerde actie.');
    }

    // --- Profielfoto upload verwerken ---
    $profielfotoPad = $user['Profielfoto'];
    if (isset($_FILES['profielfoto']) && $_FILES['profielfoto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = uniqid() . '-' . basename($_FILES['profielfoto']['name']);
        $targetFile = $uploadDir . $filename;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['profielfoto']['tmp_name'], $targetFile)) {
                $profielfotoPad = $targetFile;
            } else { $errors[] = "Fout bij het uploaden van de afbeelding."; }
        } else { $errors[] = "Alleen JPG, JPEG, PNG & GIF bestanden zijn toegestaan."; }
    }

    // --- Validatie ---
    if ($wachtwoord !== $wachtwoord_herhaal) {
        $errors[] = "De ingevoerde wachtwoorden komen niet overeen.";
    }

    if (empty($errors)) {
        if ($userIdToUpdate) { // --- UPDATE ---
            // Update basisgegevens (alleen door superuser)
            if (has_role('superuser')) {
                // Admin mag niet zijn eigen rol verlagen
                if ($isSelfEdit && $_SESSION['user_rol'] == 'admin' && $rol != 'admin') {
                    $rol = 'admin'; // Forceer rol terug naar admin
                }
                $stmt = $db_connect->prepare("UPDATE Gebruikers SET Gebruikersnaam = ?, VolledigeNaam = ?, Profielfoto = ?, Rol = ?, IsActief = ? WHERE UserID = ?");
                $stmt->bind_param("ssssii", $gebruikersnaam, $volledigeNaam, $profielfotoPad, $rol, $isActief, $userIdToUpdate);
                $stmt->execute();
            } elseif ($isSelfEdit) {
                // User mag alleen naam en foto aanpassen
                $stmt = $db_connect->prepare("UPDATE Gebruikers SET VolledigeNaam = ?, Profielfoto = ? WHERE UserID = ?");
                $stmt->bind_param("ssi", $volledigeNaam, $profielfotoPad, $userIdToUpdate);
                $stmt->execute();
            }

            // Update wachtwoord (als ingevuld)
            if (!empty($wachtwoord)) {
                 $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT);
                 $stmt_pw = $db_connect->prepare("UPDATE Gebruikers SET Wachtwoord = ? WHERE UserID = ?");
                 $stmt_pw->bind_param("si", $hashedPassword, $userIdToUpdate);
                 $stmt_pw->execute();
            }
        } else { // --- INSERT (alleen superuser) ---
            if (has_role('superuser')) {
                $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT);
                $stmt = $db_connect->prepare("INSERT INTO Gebruikers (Gebruikersnaam, VolledigeNaam, Profielfoto, Wachtwoord, Rol, IsActief) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $gebruikersnaam, $volledigeNaam, $profielfotoPad, $hashedPassword, $rol, $isActief);
                $stmt->execute();
            }
        }
        header("Location: instellingen.php");
        exit;
    }
}
?>

<section class="content-page">
    <div class="page-header">
        <h2>Gebruiker <?php echo $formAction; ?></h2>
        <a href="instellingen.php" class="action-button-header-secondary"><i class="fa-solid fa-xmark"></i> Annuleren</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Fouten gevonden:</strong>
            <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <div class="form-page-container">
        <div class="form-main-content">
            <form action="gebruiker_form.php?id=<?php echo $targetUserId; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="UserID" value="<?php echo htmlspecialchars($user['UserID']); ?>">
                
                <fieldset <?php if (!$isSelfEdit && !has_role('superuser')) echo 'disabled'; ?>>
                    <legend>Gebruikersdetails</legend>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="Gebruikersnaam">Gebruikersnaam</label>
                            <input type="text" id="Gebruikersnaam" name="Gebruikersnaam" value="<?php echo htmlspecialchars($user['Gebruikersnaam']); ?>" required <?php if (!has_role('superuser')) echo 'disabled'; ?>>
                        </div>
                        <div class="form-group">
                            <label for="VolledigeNaam">Volledige Naam</label>
                            <input type="text" id="VolledigeNaam" name="VolledigeNaam" value="<?php echo htmlspecialchars($user['VolledigeNaam']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="Rol">Rol</label>
                            <select id="Rol" name="Rol" <?php if (!has_role('admin') || ($isSelfEdit && $user['Rol'] == 'admin')) echo 'disabled'; ?>>
                                <option value="viewer" <?php echo ($user['Rol'] == 'viewer') ? 'selected' : ''; ?>>Viewer</option>
                                <option value="user" <?php echo ($user['Rol'] == 'user') ? 'selected' : ''; ?>>User</option>
                                <option value="superuser" <?php echo ($user['Rol'] == 'superuser') ? 'selected' : ''; ?>>Superuser</option>
                                <option value="admin" <?php echo ($user['Rol'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="IsActief">Status</label>
                            <select id="IsActief" name="IsActief" <?php if (!has_role('superuser')) echo 'disabled'; ?>>
                                <option value="1" <?php echo ($user['IsActief'] == 1) ? 'selected' : ''; ?>>Actief</option>
                                <option value="0" <?php echo ($user['IsActief'] == 0) ? 'selected' : ''; ?>>Inactief</option>
                            </select>
                        </div>
                        <div class="form-group form-group-full">
                            <label for="profielfoto">Profielfoto</label>
                            <?php if (!empty($user['Profielfoto'])): ?>
                                <img src="<?php echo htmlspecialchars($user['Profielfoto']); ?>" alt="Huidige profielfoto" class="form-avatar-preview">
                            <?php endif; ?>
                            <input type="file" id="profielfoto" name="profielfoto">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Wachtwoord Wijzigen</legend>
                    <p style="margin-top: -1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">Laat leeg om het huidige wachtwoord te behouden.</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="wachtwoord">Nieuw wachtwoord</label>
                            <input type="password" id="wachtwoord" name="wachtwoord">
                        </div>
                        <div class="form-group">
                            <label for="wachtwoord_herhaal">Herhaal wachtwoord</label>
                            <input type="password" id="wachtwoord_herhaal" name="wachtwoord_herhaal">
                        </div>
                    </div>
                </fieldset>
                
                <div class="form-group form-group-full">
                    <button type="submit" class="action-button-header"><?php echo $formAction; ?> Gebruiker</button>
                </div>
            </form>
        </div>
        <div class="form-sidebar">
            <div class="info-card">
                <h3><i class="fa-solid fa-shield-halved"></i> Gebruikersrollen</h3>
                <p>De rol van een gebruiker bepaalt wat hij of zij kan zien en doen in SkibsLog.</p>
                <ul>
                    <li><strong>Admin:</strong> Kan alles, inclusief gebruikersbeheer.</li>
                    <li><strong>Superuser:</strong> Kan gebruikers aanmaken en data verwijderen.</li>
                    <li><strong>User:</strong> Kan alle data aanmaken en bewerken.</li>
                    <li><strong>Viewer:</strong> Kan alleen data bekijken.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
