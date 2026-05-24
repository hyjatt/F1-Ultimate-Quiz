<?php
session_start();
require 'db.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: admin-login.php");
    exit();
}

$message = "";

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_user') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM users WHERE id = $id");
        $message = "Driver deleted from the grid.";
    } elseif ($action === 'add_user') {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $team = $conn->real_escape_string($_POST['team']);
        $password = $_POST['password']; 
        $conn->query("INSERT INTO users (username, email, team, password, super_license_points) VALUES ('$username', '$email', '$team', '$password', 0)");
        $message = "New driver added to the grid.";
    } elseif ($action === 'edit_user') {
        $id = (int)$_POST['id'];
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $team = $conn->real_escape_string($_POST['team']);
        $points = (int)$_POST['points'];
        $conn->query("UPDATE users SET username='$username', email='$email', team='$team', super_license_points=$points WHERE id=$id");
        $message = "Driver telemetry successfully updated.";
    }

    if ($action === 'delete_question') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM questions WHERE id = $id");
        $message = "Question removed from telemetry.";
    } elseif ($action === 'add_question') {
        $q = $conn->real_escape_string($_POST['question']);
        $opt_a = $conn->real_escape_string($_POST['option_a']);
        $opt_b = $conn->real_escape_string($_POST['option_b']);
        $opt_c = $conn->real_escape_string($_POST['option_c']);
        $opt_d = $conn->real_escape_string($_POST['option_d']);
        $ans = $conn->real_escape_string($_POST['correct_answer']);
        $diff = $conn->real_escape_string($_POST['difficulty']);
        $conn->query("INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_answer, difficulty) VALUES ('$q', '$opt_a', '$opt_b', '$opt_c', '$opt_d', '$ans', '$diff')");
        $message = "New telemetry question initialized.";
    } elseif ($action === 'edit_question') {
        $id = (int)$_POST['id'];
        $q = $conn->real_escape_string($_POST['question']);
        $opt_a = $conn->real_escape_string($_POST['option_a']);
        $opt_b = $conn->real_escape_string($_POST['option_b']);
        $opt_c = $conn->real_escape_string($_POST['option_c']);
        $opt_d = $conn->real_escape_string($_POST['option_d']);
        $ans = $conn->real_escape_string($_POST['correct_answer']);
        $diff = $conn->real_escape_string($_POST['difficulty']);
        $conn->query("UPDATE questions SET question='$q', option_a='$opt_a', option_b='$opt_b', option_c='$opt_c', option_d='$opt_d', correct_answer='$ans', difficulty='$diff' WHERE id=$id");
        $message = "Telemetry question data updated.";
    }
}

// Fetch Data for Tables
$users = $conn->query("SELECT id, username, email, team, super_license_points FROM users ORDER BY super_license_points DESC");
$questions = $conn->query("SELECT * FROM questions ORDER BY id DESC");

// Fetch New Metrics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_questions = $conn->query("SELECT COUNT(*) as count FROM questions")->fetch_assoc()['count'];
$activity_logs = $conn->query("SELECT u.username, a.action, a.timestamp FROM activity_logs a JOIN users u ON a.user_id = u.id ORDER BY a.timestamp DESC LIMIT 15");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Grid Quiz - Admin Pit Wall</title>
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/admin-dashboard.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="pit-wall-nav">
        <div class="logo">F1 <span>ADMIN PIT WALL</span></div>
        <div class="driver-profile">
            <span class="status-indicator" style="background-color: var(--f1-red);"></span>
            <span style="color:var(--text-light); margin-right:15px;">DIRECTOR</span>
            <a href="admin-login.php" style="color:var(--text-muted); text-decoration:none; font-size:0.8rem;">[ LOGOUT ]</a>
        </div>
    </header>

    <?php if($message): ?>
        <div class="message-banner">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <main class="dashboard-container">
        
        <section class="card" style="display: flex; gap: 20px; flex-direction: row; background: transparent; box-shadow: none; border: none; margin-bottom: -10px;">
            <div class="card" style="flex: 1; padding: 20px; text-align: center; border-left: 4px solid var(--success-cyan);">
                <h3 style="color: var(--text-muted);">TOTAL DRIVERS</h3>
                <h1 style="font-size: 2.5rem; color: var(--text-light);"><?php echo $total_users; ?></h1>
            </div>
            <div class="card" style="flex: 1; padding: 20px; text-align: center; border-left: 4px solid var(--f1-red);">
                <h3 style="color: var(--text-muted);">TOTAL QUESTIONS</h3>
                <h1 style="font-size: 2.5rem; color: var(--text-light);"><?php echo $total_questions; ?></h1>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2>DRIVER ACTIVITY LOG</h2>
            </div>
            <div class="admin-table-container">
                <table>
                    <thead>
                        <tr><th>Timestamp</th><th>Driver</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while($log = $activity_logs->fetch_assoc()): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $log['timestamp']; ?></td>
                            <td style="color: var(--success-cyan); font-weight: bold;"><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
        
        <section class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div class="clickable-title" onclick="toggleSection('usersContainer', 'icon-users')">
                    <h2 style="margin:0;">MANAGE DRIVERS</h2>
                    <span id="icon-users" class="toggle-icon">▼</span>
                </div>
                
                <div class="premium-search-wrapper">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a8 8 0 0 1 5.293 13.88l5.314 5.314a1 1 0 0 1-1.414 1.414l-5.314-5.314A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 A6 6 0 0 0 10 4z"/>
                    </svg>
                    <input type="text" id="searchUsers" class="premium-search-input" placeholder="Search drivers (Name, Email, Team)..." onkeyup="filterTable('searchUsers', 'usersTable')" onclick="event.stopPropagation();">
                </div>
            </div>
            
            <div id="usersContainer">
                <form class="admin-form" method="POST">
                    <input type="hidden" name="action" value="add_user">
                    <input type="text" name="username" placeholder="Driver Username" required>
                    <input type="email" name="email" placeholder="Comms Channel (Email)" required>
                    <input type="password" name="password" placeholder="Telemetry Key (Password)" required>
                    <select name="team" required>
                        <option value="" disabled selected>Select Constructor</option>
                        <option value="redbull">Red Bull Racing</option>
                        <option value="ferrari">Scuderia Ferrari</option>
                        <option value="mclaren">McLaren F1 Team</option>
                        <option value="mercedes">Mercedes-AMG</option>
                        <option value="astonmartin">Aston Martin</option>
                        <option value="williams">Williams Racing</option>
                    </select>
                    <button type="submit" class="admin-btn">ADD DRIVER</button>
                </form>
                
                <div class="admin-table-container">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Driver Name</th>
                                <th>Email</th>
                                <th>Constructor</th>
                                <th>Points</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span style="text-transform: capitalize;"><?php echo htmlspecialchars($u['team']); ?></span></td>
                                <td><?php echo $u['super_license_points']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="admin-btn" style="background: var(--success-cyan); color: #000;" 
                                            onclick="openUserEdit(
                                                <?php echo $u['id']; ?>, 
                                                '<?php echo htmlspecialchars(addslashes($u['username'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($u['email'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($u['team'])); ?>', 
                                                <?php echo $u['super_license_points']; ?>
                                            )">EDIT</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="admin-btn danger" onclick="return confirm('Black flag this driver?');">FLAG</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div class="clickable-title" onclick="toggleSection('questionsContainer', 'icon-questions')">
                    <h2 style="margin:0;">MANAGE TELEMETRY (QUESTIONS)</h2>
                    <span id="icon-questions" class="toggle-icon">▼</span>
                </div>
                
                <div class="premium-search-wrapper">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a8 8 0 0 1 5.293 13.88l5.314 5.314a1 1 0 0 1-1.414 1.414l-5.314-5.314A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 A6 6 0 0 0 10 4z"/>
                    </svg>
                    <input type="text" id="searchQuestions" class="premium-search-input" placeholder="Search track data or answers..." onkeyup="filterTable('searchQuestions', 'questionsTable')" onclick="event.stopPropagation();">
                </div>
            </div>
            
            <div id="questionsContainer">
                <form class="admin-form" method="POST">
                    <input type="hidden" name="action" value="add_question">
                    <input type="text" name="question" class="input-wide" placeholder="Enter Full Question Text" required>
                    <input type="text" name="option_a" placeholder="Option A" required>
                    <input type="text" name="option_b" placeholder="Option B" required>
                    <input type="text" name="option_c" placeholder="Option C" required>
                    <input type="text" name="option_d" placeholder="Option D" required>
                    <input type="text" name="correct_answer" class="input-answer" placeholder="Exact Correct Answer" required>
                    <select name="difficulty" required>
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                    <button type="submit" class="admin-btn">DEPLOY QUESTION</button>
                </form>
                
                <div class="admin-table-container">
                    <table id="questionsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Difficulty</th>
                                <th>Question</th>
                                <th>Correct Answer</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($q = $questions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $q['id']; ?></td>
                                <td><span style="text-transform: capitalize; color: var(--text-muted); font-size:0.85rem;"><?php echo htmlspecialchars($q['difficulty'] ?? 'medium'); ?></span></td>
                                <td><?php echo htmlspecialchars($q['question']); ?></td>
                                <td class="answer-text"><?php echo htmlspecialchars($q['correct_answer']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="admin-btn" style="background: var(--success-cyan); color: #000;" 
                                            onclick="openQuestionEdit(
                                                <?php echo $q['id']; ?>, 
                                                '<?php echo htmlspecialchars(addslashes($q['question'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($q['option_a'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($q['option_b'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($q['option_c'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($q['option_d'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($q['correct_answer'])); ?>',
                                                '<?php echo htmlspecialchars(addslashes($q['difficulty'] ?? 'medium')); ?>'
                                            )">EDIT</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_question">
                                            <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                            <button type="submit" class="admin-btn danger" onclick="return confirm('Delete this question?');">DEL</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div id="userEditModal" class="modal-overlay">
        <div class="modal-box">
            <h2>UPDATE DRIVER TELEMETRY</h2>
            <form class="admin-form" method="POST" style="margin-bottom: 0;">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="id" id="edit_user_id">
                
                <input type="text" name="username" id="edit_username" required placeholder="Username">
                <input type="email" name="email" id="edit_email" required placeholder="Email">
                <input type="number" name="points" id="edit_points" required placeholder="Points">
                
                <select name="team" id="edit_team" required>
                    <option value="redbull">Red Bull Racing</option>
                    <option value="ferrari">Scuderia Ferrari</option>
                    <option value="mclaren">McLaren F1 Team</option>
                    <option value="mercedes">Mercedes-AMG</option>
                    <option value="astonmartin">Aston Martin</option>
                    <option value="williams">Williams Racing</option>
                </select>
                
                <div class="action-buttons input-wide">
                    <button type="submit" class="admin-btn" style="background: var(--success-cyan); color:#000;">SAVE CHANGES</button>
                    <button type="button" class="admin-btn danger" onclick="closeModals()">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <div id="questionEditModal" class="modal-overlay">
        <div class="modal-box">
            <h2>UPDATE TRACK TELEMETRY (QUESTION)</h2>
            <form class="admin-form" method="POST" style="margin-bottom: 0;">
                <input type="hidden" name="action" value="edit_question">
                <input type="hidden" name="id" id="edit_q_id">
                
                <input type="text" name="question" id="edit_q_text" class="input-wide" required placeholder="Question Text">
                <input type="text" name="option_a" id="edit_q_a" required placeholder="Option A">
                <input type="text" name="option_b" id="edit_q_b" required placeholder="Option B">
                <input type="text" name="option_c" id="edit_q_c" required placeholder="Option C">
                <input type="text" name="option_d" id="edit_q_d" required placeholder="Option D">
                <input type="text" name="correct_answer" id="edit_q_ans" class="input-answer" required placeholder="Correct Answer">
                <select name="difficulty" id="edit_q_diff" required>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
                
                <div class="action-buttons input-wide">
                    <button type="submit" class="admin-btn" style="background: var(--success-cyan); color:#000;">SAVE CHANGES</button>
                    <button type="button" class="admin-btn danger" onclick="closeModals()">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUserEdit(id, username, email, team, points) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_team').value = team.toLowerCase();
            document.getElementById('edit_points').value = points;
            document.getElementById('userEditModal').style.display = 'flex';
        }

        function openQuestionEdit(id, q, a, b, c, d, ans, diff) {
            document.getElementById('edit_q_id').value = id;
            document.getElementById('edit_q_text').value = q;
            document.getElementById('edit_q_a').value = a;
            document.getElementById('edit_q_b').value = b;
            document.getElementById('edit_q_c').value = c;
            document.getElementById('edit_q_d').value = d;
            document.getElementById('edit_q_ans').value = ans;
            if(diff) document.getElementById('edit_q_diff').value = diff;
            document.getElementById('questionEditModal').style.display = 'flex';
        }

        function closeModals() {
            document.getElementById('userEditModal').style.display = 'none';
            document.getElementById('questionEditModal').style.display = 'none';
        }

        function toggleSection(containerId, iconId) {
            const container = document.getElementById(containerId);
            const icon = document.getElementById(iconId);
            
            if (container.style.display === 'none' || container.style.display === '') {
                container.style.display = 'block';
                icon.classList.remove('minimized');
            } else {
                container.style.display = 'none';
                icon.classList.add('minimized');
            }
        }

        function filterTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const filter = input.value.toLowerCase();
            const table = document.getElementById(tableId);
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let displayRow = false;
                const tds = tr[i].getElementsByTagName('td');
                
                for (let j = 0; j < tds.length - 1; j++) {
                    if (tds[j]) {
                        const txtValue = tds[j].textContent || tds[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            displayRow = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = displayRow ? "" : "none";
            }
        }
    </script>
</body>
</html>