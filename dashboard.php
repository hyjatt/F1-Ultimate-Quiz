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

// Driver Classification Rank
$pts = $user_data['super_license_points'];
if ($pts < 300) { 
    $player_rank = "ROOKIE"; 
} elseif ($pts < 500) { 
    $player_rank = "PRO"; 
} else { 
    $player_rank = "LEGEND"; 
}

// Constructor Standings (Team Score)
$team_standings_query = "SELECT team, SUM(super_license_points) as team_points FROM users GROUP BY team ORDER BY team_points DESC";
$team_standings_result = $conn->query($team_standings_query);
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
            <div class="score-display" style="flex-wrap: wrap;">
                <div class="data-point">
                    <span class="label">DRIVER RANK</span>
                    <span class="value" style="color: var(--success-cyan); font-size: 1.8rem;"><?php echo $player_rank; ?></span>
                </div>
                <div class="data-point">
                    <span class="label">TOTAL SCORE (PTS)</span>
                    <span class="value"><?php echo $user_data['super_license_points']; ?></span>
                </div>
                <div class="data-point">
                    <span class="label">GRID POSITION</span>
                    <span class="value"><?php echo $current_rank; ?></span>
                </div>
                <div class="data-point" style="width: 100%; margin-top: 10px; flex: none;">
                    <span class="label">CONSTRUCTOR PROFILE</span>
                    <span class="value" style="font-size: 1.2rem; text-transform: uppercase;">
                        <span class="team-dot <?php echo htmlspecialchars($user_data['team']); ?>"></span>
                        <?php echo htmlspecialchars($user_data['team']); ?> Racing
                    </span>
                </div>
            </div>
            <button class="start-quiz-btn" onclick="window.location.href='index.php'">LIGHTS OUT - START QUIZ</button>
        </section>

        <section class="card standings-card">
            <div class="card-header">
                <h2>DRIVER STANDINGS</h2>
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

        <section class="card standings-card">
            <div class="card-header">
                <h2>CONSTRUCTORS' CHAMPIONSHIP</h2>
            </div>
            <table class="standings-table">
                <thead>
                    <tr><th>Pos</th><th>Constructor</th><th>Total Points</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $t_pos = 1;
                    while($t_row = $team_standings_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $t_pos++; ?></td>
                        <td style="text-transform: capitalize;">
                            <span class="team-dot <?php echo htmlspecialchars($t_row['team']); ?>"></span>
                            <?php echo htmlspecialchars($t_row['team']); ?>
                        </td>
                        <td style="font-weight: bold; color: var(--success-cyan);"><?php echo $t_row['team_points']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

    </main>
</body>
</html>