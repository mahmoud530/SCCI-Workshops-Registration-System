<?php
/**
 * Update Participant Status
 * سريع وآمن - بدون مشاكل
 */

// الأمان أولاً
session_start();
header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['workshop_logged_in']) || !isset($_SESSION['workshop_code'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// التحقق من وجود البيانات
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$participantId = intval($_POST['id']);
$newStatus = $_POST['status'];

// التحقق من صحة القيم
$allowedStatuses = ['pending', 'contacted', 'scheduled', 'rejected'];
if (!in_array($newStatus, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status value']);
    exit;
}

// الاتصال بقاعدة البيانات
require_once '../config.php';

try {
    // التحقق من أن المشارك موجود ومرتبط بالورشة
    $workshop_code = $_SESSION['workshop_code'];
    
    $checkStmt = $pdo->prepare("
        SELECT id FROM participants 
        WHERE id = ? 
        AND (first_preference = ? OR second_preference = ? OR third_preference = ?)
    ");
    $checkStmt->execute([$participantId, $workshop_code, $workshop_code, $workshop_code]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Participant not found or unauthorized']);
        exit;
    }
    
    // تحديث الحالة
    $updateStmt = $pdo->prepare("UPDATE participants SET status = ? WHERE id = ?");
    $updateStmt->execute([$newStatus, $participantId]);
    
    // النجاح
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'status' => $newStatus
    ]);
    
} catch (PDOException $e) {
    error_log('Update Status Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
