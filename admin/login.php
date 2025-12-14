<?php
/**
 * SCCI Workshop Admin - Login Page
 */

// Security Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../config.php';

// Constants
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_TIME = 300;

// Redirect if already logged in
if (isset($_SESSION['workshop_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper Functions
function verifyWorkshopPassword($workshop_code, $password) {
    global $workshops;
    if (!isset($workshops[$workshop_code])) {
        return false;
    }
    return password_verify($password, $workshops[$workshop_code]['password_hash']);
}

function sanitize($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Process Login
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workshop_code = trim($_POST['workshop'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Initialize login attempts
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }
    
    // Validate CSRF Token
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = 'Security token validation failed. Please refresh the page.';
    }
    // Check lockout
    elseif ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && (time() - $_SESSION['last_attempt']) < LOCKOUT_TIME) {
        $remaining = ceil((LOCKOUT_TIME - (time() - $_SESSION['last_attempt'])) / 60);
        $errors[] = "Too many failed attempts. Try again in {$remaining} minutes.";
    }
    // Validate inputs
    elseif (empty($workshop_code)) {
        $errors[] = 'Please select a workshop.';
    }
    elseif (empty($password)) {
        $errors[] = 'Please enter the password.';
    }
    elseif (!isset($workshops[$workshop_code])) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        $errors[] = 'Invalid workshop selected.';
    }
    // Verify password
    elseif (verifyWorkshopPassword($workshop_code, $password)) {
        // Successful login
        session_regenerate_id(true);
        
        $_SESSION['workshop_logged_in'] = true;
        $_SESSION['workshop_code'] = $workshop_code;
        $_SESSION['workshop_name'] = $workshops[$workshop_code]['name'];
        $_SESSION['last_activity'] = time();
        $_SESSION['login_attempts'] = 0;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        header('Location: dashboard.php');
        exit;
    } else {
        // Failed login
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        $remaining = MAX_LOGIN_ATTEMPTS - $_SESSION['login_attempts'];
        $errors[] = "Invalid password. You have {$remaining} attempts remaining.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop Admin Login - SCCI</title>
        <link rel="icon" type="image/png" href="../assets/img/SCCI_Logo.png">
     <link rel="stylesheet" href="../admin/assets/css/root.css">
     <link rel="stylesheet" href="../admin/assets/css/login.css">
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
                <strong>Login Attempts:</strong> <?php echo $_SESSION['login_attempts']; ?>/<?php echo MAX_LOGIN_ATTEMPTS; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="workshop">Select Workshop</label>
                <select name="workshop" id="workshop" required>
                    <option value="">-- Choose Workshop --</option>
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
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="submit-btn">Login</button>
        </form>
    </div>

    <script>
        // Auto-focus on first empty field
        document.addEventListener('DOMContentLoaded', function() {
            const workshop = document.getElementById('workshop');
            const password = document.getElementById('password');
            
            if (!workshop.value) {
                workshop.focus();
            } else {
                password.focus();
            }
        });
    </script>
</body>
</html>