<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// استقبال البيانات وتنظيفها
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
$first_preference = trim($_POST['first_preference'] ?? '');
$second_preference = trim($_POST['second_preference'] ?? '');
$third_preference = trim($_POST['third_preference'] ?? '');
$tech_skills = trim($_POST['tech_skills'] ?? '');

// دوال التحقق البسيطة
function validateName($name) {
    return strlen($name) >= 2 && strlen($name) <= 100 && preg_match('/^[a-zA-Zأ-ي\s]+$/u', $name);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 100;
}

function validatePhone($phone) {
    
    return strlen($phone) >= 10 && strlen($phone) <= 15 && ctype_digit($phone);
}

function validateWorkshop($workshop) {
    return in_array($workshop, ['Devology', 'Business', 'Techsolve', 'Appsplash', 'UI/UX']);
}

// التحقق من البيانات
if (!validateName($name)) {
    echo json_encode(['success' => false, 'field' => 'name', 'message' => 'الاسم غير صحيح']);
    exit;
}

if (!validateEmail($email)) {
    echo json_encode(['success' => false, 'field' => 'email', 'message' => 'البريد الإلكتروني غير صحيح']);
    exit;
}

if (!validatePhone($phone)) {
    echo json_encode(['success' => false, 'field' => 'phone', 'message' => 'رقم الهاتف غير صحيح']);
    exit;
}

if (!validateWorkshop($first_preference)) {
    echo json_encode(['success' => false, 'field' => 'first_preference', 'message' => 'الاختيار الأول غير صحيح']);
    exit;
}

if (!validateWorkshop($second_preference)) {
    echo json_encode(['success' => false, 'field' => 'second_preference', 'message' => 'الاختيار الثاني غير صحيح']);
    exit;
}

if (!validateWorkshop($third_preference)) {
    echo json_encode(['success' => false, 'field' => 'third_preference', 'message' => 'الاختيار الثالث غير صحيح']);
    exit;
}

// التحقق من عدم تكرار الاختيارات
$preferences = [$first_preference, $second_preference, $third_preference];
if (count(array_unique($preferences)) !== 3) {
    echo json_encode(['success' => false, 'field' => 'first_preference', 'message' => 'يجب اختيار ورك شوبس مختلفة']);
    exit;
}

// معالجة المهارات التقنية
if (!empty($tech_skills)) {
    if (strlen($tech_skills) > 500) {
        echo json_encode(['success' => false, 'field' => 'tech_skills', 'message' => 'النص طويل جداً']);
        exit;
    }
    $tech_skills = htmlspecialchars($tech_skills, ENT_QUOTES, 'UTF-8');
} else {
    $tech_skills = null;
}

try {
    // التحقق من وجود البريد مسبقاً
    $checkStmt = $pdo->prepare("SELECT id FROM participants WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'field' => 'email', 'message' => 'هذا البريد مسجل مسبقاً']);
        exit;
    }

    // إدراج البيانات
    $insertStmt = $pdo->prepare("
        INSERT INTO participants (name, email, phone, first_preference, second_preference, third_preference, tech_skills) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $insertStmt->execute([
        $name, 
        $email, 
        $phone, 
        $first_preference, 
        $second_preference, 
        $third_preference, 
        $tech_skills
    ]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'تم التسجيل بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء التسجيل']);
    }
    
} catch (PDOException $e) {
    error_log('SCCI Registration Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في النظام']);
}
?>