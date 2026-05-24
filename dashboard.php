<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current user data
$stmt = $conn->prepare("SELECT username, super_license_points, team FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Get top 10 leaderboard
$leaderboard_query = "SELECT id, username, super_license_points, team FROM users ORDER BY super_license_points DESC LIMIT 10";
$leaderboard_result = $conn->query($leaderboard_query);

// Determine user's rank
$rank_query = "SELECT COUNT(*) as rank FROM users WHERE super_license_points > ?";
$rank_stmt = $conn->prepare($rank_query);
$rank_stmt->bind_param("i", $user_data['super_license_points']);
$rank_stmt->execute();
$rank_data = $rank_stmt->get_result()->fetch_assoc();
$current_rank = "P" . ($rank_data['rank'] + 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Grid Quiz - Pit Wall</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="pit-wall-nav">
        <div class="logo">F1 <span>PIT WALL</span></div>
        <div class="driver-profile">
            <span class="status-indicator"></span>
            <a href="profile.php" class="profile-link">Driver: <span class="username"><?php echo htmlspecialchars($user_data['username']); ?></span></a>
            <a href="login.php" style="color:var(--text-muted); text-decoration:none; margin-left:15px; font-size:0.8rem;">[ LOGOUT ]</a>
        </div>
    </header>

    <main class="dashboard-container">
        
        <section class="card telemetry-card">
            <div class="card-header">
                <h2>LIVE TELEMETRY</h2>
            </div>
            <div class="score-display">
                <div class="data-point">
                    <span class="label">SUPER LICENSE POINTS</span>
                    <span class="value"><?php echo $user_data['super_license_points']; ?></span>
                </div>
                <div class="data-point">
                    <span class="label">CURRENT RANK</span>
                    <span class="value"><?php echo $current_rank; ?></span>
                </div>
            </div>
            <button class="start-quiz-btn" onclick="window.location.href='index.php'">LIGHTS OUT - START QUIZ</button>
        </section>

        <section class="card standings-card">
            <div class="card-header">
                <h2>CHAMPIONSHIP STANDINGS</h2>
            </div>
            <table class="standings-table">
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Driver</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $pos = 1;
                    while($row = $leaderboard_result->fetch_assoc()): 
                        $is_current = ($row['id'] == $user_id) ? 'class="current-user"' : '';
                    ?>
                    <tr <?php echo $is_current; ?>>
                        <td><?php echo $pos; ?></td>
                        <td><span class="team-dot <?php echo htmlspecialchars($row['team']); ?>"></span><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo $row['super_license_points']; ?></td>
                    </tr>
                    <?php 
                    $pos++;
                    endwhile; 
                    ?>
                </tbody>
            </table>
        </section>

        <section class="card intel-card">
            <div class="card-header">
                <h2>PADDOCK INTEL</h2>
            </div>
            <div class="intel-feed">
                <div class="intel-item">
                    <span class="badge fact">FACT</span>
                    <p>An F1 car generates enough aerodynamic downforce to drive upside down on the ceiling of a tunnel at speeds exceeding 193 km/h.</p>
                </div>
                <div class="intel-item">
                    <span class="badge tip">TIP</span>
                    <p>Pay close attention to tire compound terminology. Softs (Red) offer peak grip but degrade rapidly, while Hards (White) sacrifice immediate pace for longevity.</p>
                </div>
                <div class="intel-item">
                    <span class="badge local">LOCAL INTEL</span>
                    <p>The Malaysian street circuit demands precise throttle application due to its narrow apexes and minimal runoff areas.</p>
                </div>
            </div>
        </section>

    </main>
</body>
</html>