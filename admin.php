<?php
/**
 * SCCI Workshop Admin Panel - Security Enhanced
 * Fixed: Session management, CSRF protection, error handling, password verification
 */

// =============================================================================
// SECURITY CONFIGURATION
// =============================================================================

// Configure session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

session_start();
require_once 'config.php';

// Security constants
const SESSION_TIMEOUT = 1800; // 30 minutes
const MAX_LOGIN_ATTEMPTS = 3;
const LOCKOUT_TIME = 300; // 5 minutes

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Verify workshop password with proper hashing
 */
function verifyWorkshopPassword($workshop_code, $password) {
    global $workshops;
    
    if (!isset($workshops[$workshop_code])) {
        error_log("Workshop verification attempt for non-existent workshop: $workshop_code");
        return false;
    }
    
    $workshop_info = $workshops[$workshop_code];
    
    // Check if password is hashed (starts with $2y$)
    if (isset($workshop_info['password_hash']) && !empty($workshop_info['password_hash'])) {
        // Use hashed password
        return password_verify($password, $workshop_info['password_hash']);
    } elseif (isset($workshop_info['password']) && !empty($workshop_info['password'])) {
        // Fallback to plain text (should be migrated to hashed)
        error_log("Warning: Plain text password used for workshop: $workshop_code");
        return $password === $workshop_info['password'];
    }
    
    return false;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if session has expired
 */
function isSessionExpired() {
    if (!isset($_SESSION['last_activity'])) {
        return true;
    }
    return (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT;
}

/**
 * Check if user is locked out
 */
function isLockedOut() {
    if (!isset($_SESSION['login_attempts']) || !isset($_SESSION['last_attempt'])) {
        return false;
    }
    return $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && 
           (time() - $_SESSION['last_attempt']) < LOCKOUT_TIME;
}

/**
 * Regenerate session ID for security
 */
function regenerateSession() {
    session_regenerate_id(true);
}

/**
 * Sanitize output
 */
function sanitize($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// =============================================================================
// SESSION MANAGEMENT
// =============================================================================

// Generate CSRF token
generateCSRFToken();

// Check session timeout
if (isset($_SESSION['workshop_logged_in']) && isSessionExpired()) {
    session_unset();
    session_destroy();
    session_start();
    generateCSRFToken();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// =============================================================================
// AUTHENTICATION
// =============================================================================

if (!isset($_SESSION['workshop_logged_in'])) {
    $errors = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $workshop_code = trim($_POST['workshop'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        // Initialize login attempt tracking
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt'] = time();
        }
        
        // Validate CSRF token
        if (!validateCSRFToken($csrf_token)) {
            $errors[] = 'Security token validation failed. Please refresh the page and try again.';
        }
        // Check if locked out
        elseif (isLockedOut()) {
            $remaining_time = LOCKOUT_TIME - (time() - $_SESSION['last_attempt']);
            $errors[] = "Too many failed attempts. Try again in " . ceil($remaining_time / 60) . " minutes.";
        }
        // Validate inputs
        elseif (empty($workshop_code)) {
            $errors[] = 'Please select a workshop.';
        }
        elseif (empty($password)) {
            $errors[] = 'Please enter the password.';
        }
        elseif (strlen($password) > 255) {
            $errors[] = 'Password is too long.';
        }
        elseif (!isset($workshops[$workshop_code])) {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            $errors[] = 'Invalid workshop selected.';
        }
        else {
            // Verify password
            if (verifyWorkshopPassword($workshop_code, $password)) {
                // Successful login
                regenerateSession(); // Regenerate session ID for security
                
                $_SESSION['workshop_logged_in'] = true;
                $_SESSION['workshop_code'] = $workshop_code;
                $_SESSION['workshop_name'] = $workshops[$workshop_code]['name'];
                $_SESSION['last_activity'] = time();
                $_SESSION['login_attempts'] = 0; // Reset attempts
                
                // Generate new CSRF token for admin session
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } else {
                // Failed login
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
                $errors[] = 'Invalid password.';
                
                $remaining = MAX_LOGIN_ATTEMPTS - $_SESSION['login_attempts'];
                if ($remaining > 0) {
                    $errors[] = "You have {$remaining} attempts remaining.";
                }
            }
        }
    }
    
    // Display login form
    ?>
    <!DOCTYPE html>
    <html lang="en" dir="ltr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Workshop Admin Login - SCCI</title>
        <link rel="stylesheet" href="./css/admin.css">
        <link rel="icon" type="image/png" href="./img/SCCI_Logo.png">
        <style>
            .error-list {
                background: #fee2e2;
                border: 1px solid #fecaca;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 16px;
            }
            .error-list ul {
                margin: 0;
                padding-left: 20px;
                color: #dc2626;
            }
            .error-list li {
                margin-bottom: 4px;
            }
            .security-info {
                background: #eff6ff;
                border: 1px solid #dbeafe;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 16px;
                font-size: 13px;
                color: #1e40af;
            }
        </style>
    </head>
    <body>
        <div class="login-form">
            <h2>SCCI Workshop Admin</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo sanitize($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0): ?>
                <div class="security-info">
                    <strong>Login attempts:</strong> <?php echo $_SESSION['login_attempts']; ?>/<?php echo MAX_LOGIN_ATTEMPTS; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="workshop">Select Workshop</label>
                    <select name="workshop" id="workshop" required>
                        <option value="">-- Select Workshop --</option>
                        <?php foreach ($workshops as $code => $info): ?>
                            <option value="<?php echo sanitize($code); ?>" 
                                <?php echo (($_POST['workshop'] ?? '') === $code) ? 'selected' : ''; ?>>
                                <?php echo sanitize($info['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        placeholder="Enter workshop password"
                        maxlength="255"
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
        
        <script>
            // Focus on first empty field
            document.addEventListener('DOMContentLoaded', function() {
                const workshop = document.getElementById('workshop');
                const password = document.getElementById('password');
                
                if (workshop.value === '') {
                    workshop.focus();
                } else {
                    password.focus();
                }
            });
            
            // Basic client-side validation
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const workshop = document.getElementById('workshop').value;
                const password = document.getElementById('password').value;
                
                if (!workshop) {
                    alert('Please select a workshop');
                    e.preventDefault();
                    return false;
                }
                
                if (!password) {
                    alert('Please enter the password');
                    e.preventDefault();
                    return false;
                }
                
                if (password.length > 255) {
                    alert('Password is too long');
                    e.preventDefault();
                    return false;
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Update last activity for active session
$_SESSION['last_activity'] = time();

// =============================================================================
// DATA FETCHING WITH SECURE ERROR HANDLING
// =============================================================================

$workshop_code = $_SESSION['workshop_code'];
$workshop_name = $_SESSION['workshop_name'];

try {
    $stmt = $pdo->prepare("
        SELECT * FROM participants 
        WHERE first_preference = ? OR second_preference = ? OR third_preference = ?
        ORDER BY 
            CASE 
                WHEN first_preference = ? THEN 1 
                WHEN second_preference = ? THEN 2 
                WHEN third_preference = ? THEN 3 
            END,
            registration_date DESC
        LIMIT 1000
    ");
    $stmt->execute([$workshop_code, $workshop_code, $workshop_code, $workshop_code, $workshop_code, $workshop_code]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error securely without exposing details to user
    error_log('Workshop admin database error: ' . $e->getMessage() . ' - Workshop: ' . $workshop_code);
    
    // Show generic error to user
    die('
        <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
            <h3>Database Connection Error</h3>
            <p>We are experiencing technical difficulties. Please try again later or contact the administrator.</p>
            <a href="?logout=1" style="color: #dc2626; text-decoration: none;">← Return to Login</a>
        </div>
    ');
}

// =============================================================================
// STATISTICS CALCULATION
// =============================================================================

$stats = [
    'total' => 0, 
    'first' => 0, 
    'second' => 0, 
    'third' => 0, 
    'today' => 0, 
    'skills' => 0
];

$today = date('Y-m-d');

foreach ($participants as $p) {
    $stats['total']++;

    if ($p['first_preference'] === $workshop_code) {
        $stats['first']++;
    } elseif ($p['second_preference'] === $workshop_code) {
        $stats['second']++;
    } elseif ($p['third_preference'] === $workshop_code) {
        $stats['third']++;
    }

    if (date('Y-m-d', strtotime($p['registration_date'])) === $today) {
        $stats['today']++;
    }

    if (!empty(trim($p['tech_skills'] ?? ''))) {
        $stats['skills']++;
    }
}

$remaining_time = SESSION_TIMEOUT - (time() - $_SESSION['last_activity']);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($workshop_name); ?> - Admin</title>
    <link rel="icon" type="image/png" href="./img/SCCI_Logo.png">
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo sanitize($workshop_name); ?></h1>
            <p>Participants interested in this workshop</p>
            <div style="margin-top: 10px;">
                <span class="session-timer">
                    Session expires in: <span id="countdown"><?php echo gmdate('i:s', $remaining_time); ?></span>
                </span>
                <a href="?logout=1" class="logout-btn">
                    Logout
                </a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-box">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Participants</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $stats['first']; ?></h3>
                <p>First Preference</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $stats['second']; ?></h3>
                <p>Second Preference</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $stats['third']; ?></h3>
                <p>Third Preference</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $stats['today']; ?></h3>
                <p>Today's Registrations</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $stats['skills']; ?></h3>
                <p>With Tech Skills</p>
            </div>
        </div>

        <div class="search-filters">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search participants...">
            </div>
            <select class="filter-select" id="preferenceFilter">
                <option value="">All Preferences</option>
                <option value="first">First Only</option>
                <option value="second">Second Only</option>
                <option value="third">Third Only</option>
            </select>
            <select class="filter-select" id="skillsFilter">
                <option value="">All Participants</option>
                <option value="with-skills">With Skills</option>
                <option value="no-skills">No Skills</option>
            </select>
            <button class="export-btn" onclick="exportData()">Export CSV</button>
        </div>

        <div class="table-container">
            <?php if (count($participants) > 0): ?>
            <table id="participantsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Preference</th>
                        <th>Tech Skills</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $p): 
                        if ($p['first_preference'] === $workshop_code) {
                            $preference_text = 'First Preference';
                            $preference_class = 'badge-1st';
                            $preference_data = 'first';
                        } elseif ($p['second_preference'] === $workshop_code) {
                            $preference_text = 'Second Preference';
                            $preference_class = 'badge-2nd';
                            $preference_data = 'second';
                        } elseif ($p['third_preference'] === $workshop_code) {
                            $preference_text = 'Third Preference';
                            $preference_class = 'badge-3rd';
                            $preference_data = 'third';
                        }
                    ?>
                    <tr data-preference="<?php echo $preference_data; ?>">
                        <td><?php echo sanitize($p['name']); ?></td>
                        <td><?php echo sanitize($p['email']); ?></td>
                        <td><?php echo sanitize($p['phone']); ?></td>
                        <td>
                            <span class="workshop-badge <?php echo $preference_class; ?>">
                                <?php echo $preference_text; ?>
                            </span>
                        </td>
                        <td class="tech-skills" onclick="toggleSkills(this)">
                            <?php echo !empty(trim($p['tech_skills'] ?? '')) ? sanitize($p['tech_skills']) : '-'; ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($p['registration_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <h3>No participants yet</h3>
                <p>Participants will appear here once they register</p>
            </div>
            <?php endif; ?>

            <div class="no-results" id="noResults" style="display: none;">
                No matching results
            </div>
        </div>
    </div>

    <script>
        // =============================================================================
        // SESSION MANAGEMENT
        // =============================================================================
        
        // Countdown with better timeout handling
        let remainingTime = <?php echo $remaining_time; ?>;
        const countdown = document.getElementById('countdown');
        let warningShown = false;

        const timer = setInterval(function() {
            remainingTime--;
            
            if (remainingTime <= 0) {
                clearInterval(timer);
                alert('Session expired. You will be redirected to the login page.');
                window.location.reload();
                return;
            }
            
            // Show warning when 2 minutes left
            if (remainingTime <= 120 && !warningShown) {
                warningShown = true;
                if (confirm('Your session will expire in 2 minutes. Would you like to extend it?')) {
                    // Refresh page to extend session
                    window.location.reload();
                }
            }
            
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            countdown.textContent = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            
            // Change color when time is running low
            if (remainingTime <= 300) { // 5 minutes
                countdown.style.color = '#dc2626';
                countdown.style.fontWeight = 'bold';
            }
        }, 1000);

        // =============================================================================
        // TABLE FILTERING AND SEARCH
        // =============================================================================
        
        const allRows = Array.from(document.querySelectorAll('#participantsTable tbody tr'));

        function filterTable() {
            const search = document.getElementById('searchInput').value.toLowerCase().trim();
            const preference = document.getElementById('preferenceFilter').value;
            const skills = document.getElementById('skillsFilter').value;

            let visibleCount = 0;

            allRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const hasSkills = row.cells[4].textContent.trim() !== '-';
                const rowPreference = row.getAttribute('data-preference');

                const matchesSearch = !search || text.includes(search);
                const matchesPreference = !preference || rowPreference === preference;
                const matchesSkills = !skills || 
                    (skills === 'with-skills' && hasSkills) || 
                    (skills === 'no-skills' && !hasSkills);

                const show = matchesSearch && matchesPreference && matchesSkills;
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            const noResults = document.getElementById('noResults');
            const table = document.getElementById('participantsTable');

            if (allRows.length > 0) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
                table.style.display = visibleCount === 0 ? 'none' : 'table';
            }
        }

        // Add event listeners
        if (document.getElementById('searchInput')) {
            document.getElementById('searchInput').addEventListener('input', filterTable);
            document.getElementById('preferenceFilter').addEventListener('change', filterTable);
            document.getElementById('skillsFilter').addEventListener('change', filterTable);
        }

        function toggleSkills(element) {
            if (element.textContent.trim() !== '-') {
                element.classList.toggle('expanded');
            }
        }

        function exportData() {
            if (allRows.length === 0) {
                alert('No data to export');
                return;
            }

            const visibleRows = allRows.filter(row => row.style.display !== 'none');
            if (visibleRows.length === 0) {
                alert('No matching data to export. Please adjust your filters.');
                return;
            }

            let csv = 'Name,Email,Phone,Preference,Tech Skills,Registration Date\n';

            visibleRows.forEach(row => {
                const cells = Array.from(row.cells).map(cell => {
                    // Clean and escape cell content
                    let text = cell.textContent.replace(/"/g, '""').replace(/\n/g, ' ').replace(/\r/g, '');
                    return `"${text}"`;
                });
                csv += cells.join(',') + '\n';
            });

            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `<?php echo sanitize($workshop_code); ?>_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            console.log(`Exported ${visibleRows.length} records to CSV`);
        }
        
        // =============================================================================
        // INITIALIZATION
        // =============================================================================
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Workshop Admin Panel loaded successfully');
            console.log(`Workshop: <?php echo sanitize($workshop_code); ?>`);
            console.log(`Total participants: ${allRows.length}`);
        });
    </script>
</body>
</html>