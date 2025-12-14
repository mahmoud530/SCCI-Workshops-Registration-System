<?php
/**
 * Export All Participants Data to CSV
 */

session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['workshop_logged_in']) || !isset($_SESSION['workshop_code'])) {
    die('Unauthorized access. <a href="login.php">Login</a>');
}

$workshop_code = $_SESSION['workshop_code'];

// Validate workshop code from GET parameter
$requested_workshop = $_GET['workshop'] ?? '';
if ($requested_workshop !== $workshop_code) {
    die('Invalid workshop. <a href="dashboard.php">Back to Dashboard</a>');
}

try {
    // Get all participants for this workshop
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
    ");
    $stmt->execute([
        $workshop_code, $workshop_code, $workshop_code,
        $workshop_code, $workshop_code, $workshop_code
    ]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($participants)) {
        die('No data to export. <a href="dashboard.php">Back to Dashboard</a>');
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $workshop_code . '_all_participants_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV headers
    fputcsv($output, ['Name', 'Email', 'Phone', 'University', 'Faculty', 'Level', 'Preference', 'Tech Skills' ,'status','Registration Date']);
    
    // Add data rows
    foreach ($participants as $p) {
        // Determine preference level
        if ($p['first_preference'] === $workshop_code) {
            $preference = 'First Preference';
        } elseif ($p['second_preference'] === $workshop_code) {
            $preference = 'Second Preference';
        } else {
            $preference = 'Third Preference';
        }
        
        fputcsv($output, [
            $p['name'] ?? '',
            $p['email'] ?? '',
            $p['phone'] ?? '',
            $p['university'] ?? $p['universty'] ?? '',
            $p['faculty'] ?? '',
            $p['level'] ?? '',
            $preference,
            $p['tech_skills'] ?? '',
            $p['status'] ?? '',
            date('Y-m-d H:i:s', strtotime($p['registration_date']))
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    error_log('Export error: ' . $e->getMessage());
    die('Export failed. <a href="dashboard.php">Back to Dashboard</a>');
}
?>