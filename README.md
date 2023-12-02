# Post_it_proyect

This is an sticky notes library where you add to your proyect notes. It needs to works 2 things
  1. a created database in sql
  2. a php connection with a database
This library is compatible with mysqli and PDO, but now only is compatible with mysql database.

##How to install
In Example.php you have a example where you can read to operate with this library. But it you
need an explanation I try to do it with my english skills 😂😂😂. First of all one thing that
you need is a mysql database. In my case database calls 'test'
<sub>
  $connexion =  new PDO("mysql:host=localhost;dbname=test;port=3306;charset=utf8mb4", "root" , "");
  $connexion2 = new mysqli('localhost', 'root', '', 'test', 3306);
</sub>
