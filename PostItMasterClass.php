<?php
include_once './ObjectPostIt.php';
/**
 * ==================================================================================
 *                               STICKY NOTES BY 
 *                               @author Jafet Núñez  
 * ==================================================================================
 * Legend of naming versioning : LTS.things added.fix            
 *                                  
 * @version 1.0.0
 *      First release
 * @version 1.0.1
 *      JS Movement fixed, now you can move all notes where you want, and stare in
 *      their site.
 * @version 1.1.1
 *      + Button can add a new note refreshing navigator.
 * @version 1.2.1
 *      Bin button can remove a note and it apears in diferents notes than first one
 * @version 
 * ==================================================================================
 * 
 */

/**
 * This is the manager Post It class where you manage all notes in back-end an display them in front. 
 * I have to things more ideas because the main focus where you will use this library is a web application
 * Now I'll show you this parameters
 * private array $postItList -> is the place where PostIt are stored.
 * private string $database -> name of database
 * private PDO | mysqli $db -> a bbdd connection it don't care if it's PDO or mysqli
 * private string $mysqliMap -> a string where you have msqli bindnigs for mysqli queries
 * private array $pdoMap -> an array where you have the bindings for PDO queries
 * private string $dbtype -> a convinient variable to recognize what kind of connection are you using
 */
class PostItManager {
    private array $postItList = [];

    private string $database;
    
    private PDO | mysqli $db;

    private string $mysqlMap = 'ssdd';

    private array $pdoMap = [
        'header' => PDO::PARAM_STR,
        'innertext' => PDO::PARAM_STR,
        'size' => PDO::PARAM_INT,
        'user' => PDO::PARAM_INT
    ];

    private string $dbtype;

    /**
     * @method void __construct() 
     * @param PDO | mysqli $db a connection with those drivers
     * @param string $database a name of a database what are you using
     * @param int $user id of the user that use the notes.
     */
    public function __construct(PDO | mysqli $db = null, string $database = null, int $user) {
        if($db == null || $database == null){
            die('Refused connection, you need make a database connection with PDO or mysqli and set a database name to insert PostIt data');
        }else{
            $this->db = $db;
            $this->database = $database;
        }
        if($this->testDB()<0){
            $this->createTable();
        }
        if($this->userHasNote($user)==0){           
            $this->insertNote($user);
        }
    }

    /**
     * @method void userHasNote() this method validate how much notes have an user
     * @param int $user this is the id of the user in database.
     */
    private function userHasNote(int $user){
        if($this->dbtype === 'PDO'){            
            $stmt = $this->db->prepare('SELECT * from post_it WHERE user = :id');
            $stmt->bindValue(':id', $user, PDO::PARAM_INT);
            $stmt->execute();
            $this->postItList = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        if($this->dbtype === 'mysqli'){
            $stmt = $this->db->stmt_init();
            $stmt = $this->db->prepare('SELECT * from post_it WHERE user = ?');
            $stmt->bind_param('d', $user);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_object()){
                $this->postItList[] = $row;
            }
        }
        return count($this->postItList);
    }

    

    /**
     * @method void createTable() this method provides you a table where you can use your notes, if you want more fields you can
     * modify createtable.sql file to add, remove or change the main configuration
     */
    private function createTable(){
        try{
            if($this->dbtype === 'PDO'){
                $sql = file_get_contents('./createtable.sql');
                $this->db->query($sql);
            }   
            if($this->dbtype === 'mysqli'){
                $sql = file_get_contents('./createtable.sql');
                $this->db->query($sql);
            }
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * @method void testDB() this method find if your selected database have a table whose name is "post_it", if you have one 
     * this method won't allow to make other table to have notes. If you don't have it, it will make the order to create the table
     */
    private function testDB(){
        if(get_class($this->db) === 'PDO'){
            $this->dbtype = 'PDO';
            $stmt = $this->db->query("SHOW TABLES IN $this->database WHERE Tables_in_$this->database = 'post_it';");
            $stmt->execute();
            return $stmt->rowCount();
        }
        if(get_class($this->db) === 'mysqli'){
            $this->dbtype = 'mysqli';
            $stmt = $this->db->query("SHOW TABLES IN $this->database WHERE Tables_in_$this->database = 'post_it';");
            return $stmt->num_rows;
        }
    }

    /**
     *  @method void generateCSS() a way to import css 
     */
    public function generateCSS():void{
        echo '<link rel="stylesheet" href="./PostIt.css">';
    }
    /**
     *  @method void generateJS() a way to import js
     */
    public function generateJS():void{
        echo '<script src="./PostIt.js"></script>';
    }
    /**
     *  @method void generatePostIt() a way to display all notes that an user have.
     */
    public function generatePostIt(){
        if(!empty($this->postItList)){
            foreach($this->postItList as $k=>$post){
                $this->generateHTML($post, $k);
            }
        }else{
            echo "<script> 
                window.addEventListener('DOMContentLoaded', ()=>{
                    window.location.reload()
                })
            </script>";
        }
    }

    /**
     * @method void insertNote() once you have the database created you need a note, for this reason this method makes you your first note
     * where you can write, resize it, move it or... make more notes!!!
     */
    public function insertNote(int $user){
        $firstNote = new PostIt(0, "This is a title!!",  "Hello! I\'m a cute note 😂", $user, 300, 0, 0);
        if($this->dbtype === 'PDO'){
            $stmt = $this->db->prepare('INSERT INTO post_it (header, innertext, size, user, x , y) VALUES (:header , :innertext, :size , :user, :x, :y)');
            foreach($firstNote->getAll() as $key=>$value){                
                if($key!='id'){
                    $stmt->bindValue(':'.$key, $value, $this->pdoMap[$key]);
                }
            }
            $stmt->execute();
        }
        if($this->dbtype === 'mysqli'){
            $stmt = $this->db->stmt_init();
            $stmt = $this->db->prepare('INSERT INTO post_it (header, innertext, size, user) VALUES (?,?,?,?)');
            $params = $firstNote->getAll();
            $stmt->bind_param($this->mysqlMap, $params['header'], $params['innertext'], $params['size'], $params['user']);
            $stmt->execute();
        }
    }

    /**
     *  @method void generateHTML() the template
     */
    public function generateHTML($postIt, int $k){
        $firstNote = ($k!=0)?'<p class="delete">Bin</p>':'';
        echo 
        '<div class="post-it" data-id="'.$postIt->id.'" data-user="'.$postIt->user.'" data-move="false"
        style="top: '.$postIt->y.'px; left:'.$postIt->x.'px; z-index:'.($postIt->id*1000).'">
            <div class="post-it-window">
                '.$firstNote.'
                <p class="add">+</p>
                <p class="minimize">-</p>
                <p class="close">X</p>
            </div>
            <div class="child-node">
                <h4 contenteditable="true"> '.$postIt->header.' </h4>
                <p contenteditable="true"> '.$postIt->innertext.' </p>
            </div>
        </div>';
    }
    /**
     *  @method void updateNote() a method that allows you modify the notes
     */
    public function updateNote(array $params):void{
        /**
         * Esta va a tener la función de obtener los parámetros de la nota y de ahí meterla en la base de datos
         */
    }
    public function deleteNote(int $id):void{
        /**
         * Esta va a tener la función de obtener los parámetros de la nota y de ahí meterla en la base de datos
         */
        if($this->dbtype === 'PDO'){
            $stmt = $this->db->prepare('DELETE FROM post_it where id = :id ');
            $stmt->bindValue(':id', $id, $this->pdoMap['id']);
            $stmt->execute();
        }
        if($this->dbtype === 'mysqli'){
            $stmt = $this->db->stmt_init();
            $stmt = $this->db->prepare('DELETE FROM post_it where id = ? ');
            $stmt->bind_param('s', $id);
            $stmt->execute();
        }
        
    }

    /**
     * @method void jsonMode() a convenient way to get all notes of an user in a json
     */
    public function jsonMode():void{
        echo json_encode($this->postItList);
    }

}



?>