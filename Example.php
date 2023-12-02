<?php 
//test class
require_once './PostItMasterClass.php';
$connexion =  new PDO("mysql:host=localhost;dbname=test;port=3306;charset=utf8mb4", "root" , "");
$connexion2 = new mysqli('localhost', 'root', '', 'test', 3306);
$posit = new PostItManager($connexion, "test", 1);
$url_divider = explode('/', $_SERVER['PHP_SELF']);
$action = $url_divider[count($url_divider)-1];
switch($action){
    case 'addNote':
        $posit->insertNote();
        die(json_encode(['status'=>200, 'postitjson'=>$posit->jsonMode(), 'postithtml'=>$posit->generatePostIt(false)]));
    case 'removeNote':
        $posit->deleteNote(intval($_POST['id']));
        die(json_encode(['status'=>200, 'postit'=>$posit->jsonMode(), 'postithtml'=>$posit->generatePostIt(false)]));
    case 'editNote':
        $posit->updateNote(array_filter(json_decode($_POST['event'], true), fn($e)=>$e!=''));
        die(json_encode(['status'=>200, 'update_fields'=>$update_fields]));
}
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
    <?php $posit->generatePostIt(true) ?>
</body>
</html>