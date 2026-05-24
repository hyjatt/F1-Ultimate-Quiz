<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $team = $_POST['team'];
    
    if (!empty($_POST['new-password']) && $_POST['new-password'] === $_POST['confirm-password']) {
        $new_password = password_hash($_POST['new-password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET email=?, team=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $email, $team, $new_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET email=?, team=? WHERE id=?");
        $stmt->bind_param("ssi", $email, $team, $user_id);
    }
    
    if ($stmt->execute()) {
        $success_msg = "Telemetry updated successfully.";
    }
}

// Fetch current details to populate form
$stmt = $conn->prepare("SELECT username, email, team FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Grid Quiz - Driver Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="pit-wall-nav">
        <div class="logo">F1 <span>PIT WALL</span></div>
        <div class="nav-actions">
            <a href="dashboard.php" class="back-btn">RETURN TO PIT WALL</a>
        </div>
    </header>

    <main class="profile-container">
        <section class="card profile-card">
            <div class="card-header">
                <h2>DRIVER IDENTIFICATION</h2>
                <p>Update your paddock credentials and team allegiance.</p>
                <?php if($success_msg): ?><p style="color:#00d2be; margin-top:10px;"><b><?php echo $success_msg; ?></b></p><?php endif; ?>
            </div>
            
            <form class="profile-form" method="POST" action="profile.php">
                <div class="form-row">
                    <div class="input-group">
                        <label for="username">Driver Name</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled style="opacity: 0.5; cursor: not-allowed;">
                    </div>
                    <div class="input-group">
                        <label for="email">Comms Channel (Email)</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                </div>

                <div class="input-group full-width">
                    <label for="team">Constructor Allegiance (Favorite Team)</label>
                    <select id="team" name="team">
                        <option value="redbull" <?php if($user_data['team'] == 'redbull') echo 'selected'; ?>>Red Bull Racing</option>
                        <option value="ferrari" <?php if($user_data['team'] == 'ferrari') echo 'selected'; ?>>Scuderia Ferrari</option>
                        <option value="mclaren" <?php if($user_data['team'] == 'mclaren') echo 'selected'; ?>>McLaren F1 Team</option>
                        <option value="mercedes" <?php if($user_data['team'] == 'mercedes') echo 'selected'; ?>>Mercedes-AMG PETRONAS</option>
                        <option value="astonmartin" <?php if($user_data['team'] == 'astonmartin') echo 'selected'; ?>>Aston Martin Aramco</option>
                        <option value="williams" <?php if($user_data['team'] == 'williams') echo 'selected'; ?>>Williams Racing</option>
                    </select>
                </div>

                <div class="security-section">
                    <h3>SECURITY PROTOCOLS</h3>
                    <div class="form-row">
                        <div class="input-group">
                            <label for="new-password">New Telemetry Key</label>
                            <input type="password" id="new-password" name="new-password" placeholder="Leave blank to keep current">
                        </div>
                        <div class="input-group">
                            <label for="confirm-password">Confirm New Key</label>
                            <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm new password">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="save-btn">BOX CONFIRM - SAVE CHANGES</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>