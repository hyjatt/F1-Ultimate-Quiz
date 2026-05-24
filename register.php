<?php
session_start();
require 'db.php';

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $team = $_POST['team'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, email, team, super_license_points) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $username, $password, $email, $team);

    if ($stmt->execute()) {
        $success_msg = "Super License granted! You can now login.";
    } else {
        $error_msg = "Username or email already exists on the grid.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Grid Quiz - Register</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>F1 <span>PIT WALL</span> REGISTRATION</h1>
            <p>Apply for your Super License.</p>
        </div>
        
        <?php if($error_msg): ?> <p style="color:var(--f1-red); text-align:center; margin-bottom:15px;"><b><?php echo $error_msg; ?></b></p> <?php endif; ?>
        <?php if($success_msg): ?> <p style="color:#00d2be; text-align:center; margin-bottom:15px;"><b><?php echo $success_msg; ?></b></p> <?php endif; ?>

        <form class="login-form" method="POST" action="register.php">
            <div class="input-group">
                <label for="username">Driver Name (Username)</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="email">Comms Channel (Email)</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="team">Constructor Allegiance</label>
                <select id="team" name="team" style="background-color: var(--f1-bg-main); border: 1px solid #333; color: var(--text-light); padding: 14px 15px; border-radius: 4px; font-size: 1rem; width:100%; margin-top:5px;">
                    <option value="redbull">Red Bull Racing</option>
                    <option value="ferrari">Scuderia Ferrari</option>
                    <option value="mclaren">McLaren F1 Team</option>
                    <option value="mercedes">Mercedes-AMG PETRONAS</option>
                </select>
            </div>
            <div class="input-group">
                <label for="password">Telemetry Key (Password)</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">BOX CONFIRM - SIGN UP</button>
        </form>
        
        <div class="signup-link">
            <p>Already on the grid? <a href="login.php">Return to Pit Wall</a></p>
        </div>
    </div>
</body>
</html>