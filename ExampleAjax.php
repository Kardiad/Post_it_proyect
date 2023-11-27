<?php
//$connexion =  new PDO("mysql:host=localhost;dbname=test;port=3306;charset=utf8mb4", "root" , "");
$connexion2 = new mysqli('localhost', 'root', '', 'test', 3306);
$posit = new PostItManager($connexion2, "test", 1);

?>