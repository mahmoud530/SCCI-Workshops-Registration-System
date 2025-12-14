<?php
date_default_timezone_set('Africa/Cairo');

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
//   'Devology' => 'dev123880',
//    'Marketnuer' => 'bus123771',
//    'Techsolve' => 'tech123662',
//    'Data Station' => 'data123553'
// الورك شوبس وكلمات المرور (مطابقة للفورم)
$workshops = [
    'Devology' => [
        'name' => 'Devology',
        'description' => ' Full stack Web-Development Workshop',
        'password_hash' => '$2y$10$Fy6zUxbcen9sBiN9FuTVOOrVOLY3OsU7UvqnrXsInj3ILmpGwiFwy' // dev123880
    ],
    'Marketnuer' => [
        'name' => 'Marketnuer',
        'description' => ' Entrepreneur & Marketing Workshop',
        'password_hash' => '$2y$10$KUPlNd1wt1Oa1HAS7TVlH.cEbjdA4jSFD3.3kxCXZpTiPtPXAl966' // bus123771
    ],
    'Techsolve' => [
        'name' => 'Techsolve',
        'description' => 'Technology Solutions Workshop',
        'password_hash' => '$2y$10$8an1YW6KAfq9PUsLgBBj/ePSCe08OxuX7krGZRNgVb31xldI/Hub.' // tech123662
    ],'Data Station'=> [
        'name' => 'Data Station',
        'description' => 'Data-Analsyis & Machine-learning Workshop',
        'password_hash' => '$2y$10$Bv5vQooubWWuD77UD/hQyuXTehQMRBftkuTAdsUV/xU0nDBfvxnUe' //data123553
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