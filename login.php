<?php
// We hebben de config nodig voor de databaseverbinding en sessie
require_once 'config.php';

$error_message = '';

// Controleer of de gebruiker al is ingelogd, zo ja, stuur door naar het dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Verwerk het inlogformulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = $_POST['gebruikersnaam'] ?? '';
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if (empty($gebruikersnaam) || empty($wachtwoord)) {
        $error_message = 'Vul alstublieft beide velden in.';
    } else {
        $stmt = $db_connect->prepare("SELECT UserID, Wachtwoord, VolledigeNaam, Rol FROM Gebruikers WHERE Gebruikersnaam = ? AND IsActief = 1");
        $stmt->bind_param("s", $gebruikersnaam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verifieer het gehashte wachtwoord
            if (password_verify($wachtwoord, $user['Wachtwoord'])) {
                // Wachtwoord is correct, start de sessie
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['user_naam'] = $user['VolledigeNaam'];
                $_SESSION['user_rol'] = $user['Rol'];

                header("Location: index.php");
                exit;
            }
        }
        $error_message = 'De combinatie van gebruikersnaam en wachtwoord is onjuist.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - SkibsLog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Specifieke stijlen voor de login pagina */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--background-color);
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            background-color: var(--widget-background);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }
        .login-header .fa-anchor {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }
        .login-header h1 {
            font-size: 2rem;
            font-weight: 600;
        }
        .login-error {
            background-color: #ffebe6;
            color: #c53030;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fa-solid fa-anchor"></i>
            <h1>SkibsLog</h1>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="login-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="gebruikersnaam">Gebruikersnaam</label>
                <input type="text" id="gebruikersnaam" name="gebruikersnaam" required>
            </div>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="wachtwoord">Wachtwoord</label>
                <input type="password" id="wachtwoord" name="wachtwoord" required>
            </div>
            <div class="form-group">
                <button type="submit" class="action-button-header" style="width: 100%;">Inloggen</button>
            </div>
        </form>
    </div>
</body>
</html>
