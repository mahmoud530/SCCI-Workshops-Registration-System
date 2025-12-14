<?php
// if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    // $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // header("Location: $redirect");
    // exit();
// }

// عرض الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// توصيل بقاعدة البيانات
include 'config.php';

// توجيه المستخدم مباشرةً إلى home.php
header("Location: registration_Form.php");
exit();
?>
