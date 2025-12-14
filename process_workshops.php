<?php
/**
 * =====================================================
 * SCCI Workshop Registration - Process Form
 * معالجة نموذج التسجيل في الورش
 * =====================================================
 */

// =====================================================
// SECTION 1: Initial Setup & Security Headers
// الإعداد الأولي ورؤوس الأمان
// =====================================================

require_once'check_registration.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// =====================================================
// SECTION 2: Request Method Validation
// التحقق من طريقة الطلب
// =====================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// =====================================================
// SECTION 3: Rate Limiting (Fixed Version)
// منع السبام – نسخة محسّنة تمنع الريسيت بعد F5
// =====================================================
session_start();

// جهّز مفتاح ثابت للجلسة علشان السبام ما يتصفرش
if (!isset($_SESSION['reg_limit'])) {
    $_SESSION['reg_limit'] = [
        'count' => 0,          // عدد التسجيلات
        'start_time' => time() // بداية الساعة
    ];
}

$currentTime = time();
$limitData = &$_SESSION['reg_limit'];

// لو عدّت ساعة → صفّر العداد
if ($currentTime - $limitData['start_time'] > 3600) {
    $limitData['count'] = 0;
    $limitData['start_time'] = $currentTime;
}

// لو عدّى 3 تسجيلات → امنع
if ($limitData['count'] >= 3) {
    http_response_code(429);

    $remaining = 3600 - ($currentTime - $limitData['start_time']);

    echo json_encode([
        'success' => false,
        'limit' => true,
        'remaining' => $remaining,
        'message' => 'تم تجاوز الحد المسموح. حاول مرة أخرى بعد ساعة'
    ]);
    exit;
}
// =====================================================
// SECTION 4: Data Receiving & Cleaning
// استقبال البيانات وتنظيفها
// =====================================================
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = preg_replace('/\D/', '', trim($_POST['phone'] ?? ''));
$university = trim($_POST['university'] ?? '');
$faculty = trim($_POST['faculty'] ?? '');
$level = trim($_POST['level'] ?? '');
$first_preference = trim($_POST['first_preference'] ?? '');
$second_preference = trim($_POST['second_preference'] ?? '');
$third_preference = trim($_POST['third_preference'] ?? '');
$tech_skills = trim($_POST['tech_skills'] ?? '');

// =====================================================
// SECTION 5: Validation Functions
// دوال التحقق من صحة البيانات
// =====================================================

function validateName($name) {
    return !empty($name) &&
        strlen($name) >= 2 &&
        strlen($name) <= 100 &&
        preg_match('/^[a-zA-Zأ-ي\s\.\-0-9]+$/u', $name);
}

function validateEmail($email) {
    return !empty($email) &&
        strlen($email) <= 100 &&
        filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    $len = strlen($phone);
    return $len === 10 || $len === 11;
}

function validateUniversity($university) {
    return !empty($university) &&
        strlen($university) >= 2 &&
        strlen($university) <= 100 &&
        preg_match('/^[a-zA-Zأ-ي\s0-9\-\(\)]+$/u', $university);
}

function validateFaculty($faculty) {
    return !empty($faculty) &&
        strlen($faculty) >= 2 &&
        strlen($faculty) <= 100 &&
        preg_match('/^[a-zA-Zأ-ي\s0-9\-\(\)]+$/u', $faculty);
}


function validateLevel($level) {
    return !empty($level) &&
        strlen($level) >= 1 &&
        strlen($level) <= 50 &&
        preg_match('/^[a-zA-Zأ-ي\s0-9]+$/u', $level);
}

function validateTechSkills($skills) {
    return strlen($skills) <= 500 && !preg_match('/<[^>]*>/', $skills);
}

function validateWorkshop($workshop) {
    return in_array($workshop, ['Devology','Marketnuer','Techsolve','Data Station'], true);
}

// =====================================================
// SECTION 6: Input Validation
// =====================================================
if (!validateName($name)) exitJson('name','الاسم غير صحيح');
if (!validateEmail($email)) exitJson('email','البريد الإلكتروني غير صالح');
if (!validatePhone($phone)) exitJson('phone','رقم الهاتف غير صحيح');
if (!validateUniversity($university)) exitJson('university','اسم الجامعة غير صحيح');
if (!validateFaculty($faculty)) exitJson('faculty','اسم الكلية غير صحيح');
if (!validateLevel($level)) exitJson('level','المستوى الدراسي غير صحيح');

if (!validateWorkshop($first_preference)) exitJson('first_preference','الاختيار الأول غير صحيح');
if (!validateWorkshop($second_preference)) exitJson('second_preference','الاختيار الثاني غير صحيح');
if (!validateWorkshop($third_preference)) exitJson('third_preference','الاختيار الثالث غير صحيح');

if (count(array_unique([$first_preference,$second_preference,$third_preference])) !== 3) {
    exitJson('first_preference','يجب اختيار ورش مختلفة');
}

if (!validateTechSkills($tech_skills)) exitJson('tech_skills','المهارات غير صالحة');
if (!empty($tech_skills)) $tech_skills = htmlspecialchars($tech_skills, ENT_QUOTES, 'UTF-8');

// دالة إخراج خطأ موحد
function exitJson($field,$msg){
    echo json_encode(['success'=>false,'field'=>$field,'message'=>$msg]);
    exit;
}

// =====================================================
// SECTION 7: Database Operations
// =====================================================
try {

    // تحقق من البريد
    $checkStmt = $pdo->prepare("SELECT id FROM participants WHERE email=?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        exitJson('email','هذا البريد مسجل مسبقاً');
    }

    // إدراج البيانات
    $insert = $pdo->prepare("
        INSERT INTO participants (
            name,email,phone,university,faculty,level,
            first_preference,second_preference,third_preference,
            tech_skills,registration_date
        ) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())
    ");

    $success = $insert->execute([
        $name,$email,$phone,$university,$faculty,$level,
        $first_preference,$second_preference,$third_preference,$tech_skills
    ]);

    if ($success) {

        // زيادة عداد التسجيلات
        $_SESSION['reg_limit']['count']++;

        error_log("SCCI Registration OK: $email");

        echo json_encode([
            'success'=>true,
            'message'=>'تم التسجيل بنجاح! سيتم التواصل معك قريباً'
        ]);
    } else {
        error_log("SCCI Registration Failed: $email");
        echo json_encode(['success'=>false,'message'=>'حدث خطأ، حاول مرة أخرى']);
    }

} catch (PDOException $e) {

    error_log("DB ERROR: ".$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'خطأ في النظام. حاول لاحقاً']);
}

?>
