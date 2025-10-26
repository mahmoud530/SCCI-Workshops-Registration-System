    <?php
    // استخدم هذا الملف مرة واحدة فقط لتوليد كلمات مرور مشفرة للورك شوبس
    // ثم احذف الملف بعد نسخ الهاشات

    $workshops_passwords = [
        'Devology' => 'dev123',
        'Business' => 'bus123', 
        'Techsolve' => 'tech123',
        'Appsplash' => 'app123',
        'UI/UX' => 'ui123'
    ];

    echo "كلمات المرور المشفرة للورك شوبس:\n\n";

    foreach ($workshops_passwords as $workshop => $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        echo "'{$workshop}' => [\n";
        echo "    'password_hash' => '{$hash}',\n";
        echo "    'password' => '{$password}' // احذف هذا السطر بعد النسخ\n";
        echo "],\n\n";
    }

    echo "\nانسخ الهاشات واستبدلها في ملف config.php\n";
    echo "ثم احذف هذا الملف فوراً!\n";
    ?>