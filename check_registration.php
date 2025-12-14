<?php
require_once 'config.php';
$stmt = $pdo->prepare("
    SELECT value 
    FROM settings 
    WHERE name = 'registration_open'
    LIMIT 1
");
$stmt->execute();
$setting = $stmt->fetch();

// لو الفورم مقفول → روح على closed.php
if (!$setting || $setting['value'] !== '1') {
    header("Location: closed.php");
    exit;
}
?>