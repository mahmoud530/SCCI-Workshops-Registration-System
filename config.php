<?php
// إعدادات قاعدة البيانات
$host = 'localhost';
$dbname = 'scci_form';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// الورك شوبس وكلمات المرور (مطابقة للفورم)
$workshops = [
    'Devology' => [
        'name' => 'Devology',
        'description' => 'Development Workshop',
        'password_hash' => '$2y$10$fwsBhVgz0sojelvz5dWfVuMIyK4nyKZMimQbonqeLQ2TGTEKCUj/u' // dev123
    ],
    'Business' => [
        'name' => 'Business & Finance',
        'description' => 'Investment Workshop',
        'password_hash' => '$2y$10$tU36YEC1hIW3fdbtTz1PZ.eWQcz8ELSkXcsVuhHSVvnenkgxm2PDy' // bus123
    ],
    'Techsolve' => [
        'name' => 'Techsolve',
        'description' => 'Technology Solutions Workshop',
        'password_hash' => '$2y$10$viYVfgXu1fgSm0rxYTiuTuJlM.5OhqA54vss4nLonzoOQ0pXeX9OK' // tech123
    ],
    'Appsplash' => [
        'name' => 'AppSplash',
        'description' => 'Mobile Applications Workshop',
        'password_hash' => '$2y$10$XB.l93G9cInShNU2CGYf2OLGLKT29Yopz82AgAb4zwMhyhTfSM.v6' // app123
    ],
    'UI/UX' => [
        'name' => 'UI/UX Design',
        'description' => 'User Interface and Experience Workshop',
        'password_hash' => '$2y$10$8hHcyDyn5xOPZzVP9TR03eiYPl.SNjO/tN0zUdP98X1NQ2hYpiixq' // ui123
    ]
];

// دالة للحصول على اسم الورك شوب
function getWorkshopName($code) {
    global $workshops;
    return $workshops[$code]['name'] ?? 'Unknown Workshop';
}

// دالة للحصول على جميع أكواد الورك شوبس
function getAllWorkshopCodes() {
    global $workshops;
    return array_keys($workshops);
}
?>