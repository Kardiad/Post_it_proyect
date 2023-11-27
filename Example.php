<?php 
//test class
require_once './PostItMasterClass.php';
//$connexion =  new PDO("mysql:host=localhost;dbname=test;port=3306;charset=utf8mb4", "root" , "");
$connexion2 = new mysqli('localhost', 'root', '', 'test', 3306);
$posit = new PostItManager($connexion2, "test", 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php $posit->generateCSS() ?>
    <?php $posit->generateJS() ?>
</head>
<body>
    <?php $posit->generatePostIt() ?>
</body>
</html>