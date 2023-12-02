# Post_it_proyect

This is an sticky notes library where you add to your proyect notes. It needs to works 2 things
  1. a created database in sql
  2. a php connection with a database
This library is compatible with mysqli and PDO, but now only is compatible with mysql database.

##How to install
In Example.php you have a example where you can read to operate with this library. But it you
need an explanation I try to do it with my english skills 😂😂😂. First of all one thing that
you need is a mysql database. In my case database calls 'test'
```
  $connexion =  new PDO("mysql:host=localhost;dbname=test;port=3306;charset=utf8mb4", "root" , "");
  $connexion2 = new mysqli('localhost', 'root', '', 'test', 3306);
```
Once you have done the connections you will need to instanciate the class PostItManager in PostItManager.php
file as you can see below
```
  $posit = new PostItManager($connexion, "test", 1);
```
Where:
  1.  $connection = a PDO or mysqli database connection
  2.  "test" = an string with database name
  3.  1 = a integer user id, or person who did it notes.
In second place we have a basic router to do all ajax connections like this:
```
  switch($action){
      case 'addNote':
          $posit->insertNote();
          header('Content-Type: text/plain');
          echo json_encode(['status'=>200, 'postitjson'=>$posit->jsonMode(), 'postithtml'=>$posit->generatePostIt(false)]);
          exit;
      case 'removeNote':
          $posit->deleteNote(intval($_POST['id']));
          die(json_encode(['status'=>200, 'postit'=>$posit->jsonMode(), 'postithtml'=>$posit->generatePostIt(false)]));
      case 'editNote':
          $update_fields = array_filter(json_decode($_POST['event'], true), fn($e)=>$e!='');
          $posit->updateNote($update_fields);
          die(json_encode(['status'=>200, 'update_fields'=>$update_fields]));
  }
```
And finally we have to make styles, js and html visible and we can do it with:
```
  $posit->generateCSS();
  $posit->generateJS();
  $posit->generatePostIt(true);
```
The first instruction add the css stylesheet line
The second one add the JS script line
and finaly the last one make all html based in the list of all notes that you have. The parameter true is required, because if 
it's true you can get all html and if it is false you recive a JSON with all notes.
