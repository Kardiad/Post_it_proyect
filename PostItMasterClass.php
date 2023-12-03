<?php
include_once './ObjectPostIt.php';
/**
 * ==================================================================================
 *                               STICKY NOTES BY 
 *                               @author Jafet Núñez  
 *                               VERSION HISTORIAL
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
 * @version 1.2.2
 *      Fix table admits emojis
 * @version 1.3.2
 *      Implemented ajax reload when you add a note and remove it
 * @version 1.3.2 (1/2)
 *      Observer in JavaScript recognizes h4 and p inside notes
 * @version 1.3.3 (1/2)
 *      Improve security in delete notes, now recognizes the true owner of this
 * @version 1.8.8 (2/2)
 *      Seriously I don't know how call this version because I fixed a lot of things
 *      Update in database for all fields
 *      Fix table for styles now it is one field
 *      Event loop in front end to optimize pull request and observable desing    
 *      Table now admits emojis 😀
 * @version 1.8.9 
 *      Movemet is fixed at this frame, in other words, you can't move the note in other 
 *      width and height that the window
 * @version 1.9.9
 *      Basic copy paste implements
 * @version 1.10.9
 *      Now you can change color to notes using doubleclick 😁
 * @version 1.10.10
 *      Fix problems with color selector
 * @version 1.10.11
 *      Fix problems with copy pasting
 * @version 1.11.11
 *      Improvements with notes styles and usability issues
 * @version 1.11.12
 *      Fix close option
 * ==================================================================================
 *                                      WISHLIST
 * ==================================================================================
 *  2º Options with rightclick
 *      2.1 -> add images
 *      2.2 -> paste text
 *  3º Customize color or background    
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

    private array $mysqlMap = [
        'header' => 's',
        'innertext' => 's',
        'user' => 'd',
        'styles' => 's'
    ];

    private array $pdoMap = [
        'header' => PDO::PARAM_STR,
        'innertext' => PDO::PARAM_STR,
        'user' => PDO::PARAM_INT,
        'styles' => PDO::PARAM_STR
    ];

    private string $dbtype;

    private int $user;

    /**
     * @method void __construct() 
     * @param PDO | mysqli $db a connection with those drivers
     * @param string $database a name of a database what are you using
     * @param int $user id of the user that use the notes.
     */
    public function __construct(PDO | mysqli $db = null, string $database = null, int $user) {
        $this->user = $user;
        if($db == null || $database == null){
            die('Refused connection, you need make a database connection with PDO or mysqli and set a database name to insert PostIt data');
        }else{
            $this->db = $db;
            $this->database = $database;
        }
        if($this->testDB()==0){
            $this->createTable();
        }
        if($this->userHasNote()==0){           
            $this->insertNote();
        }
    }

    /**
     * @method void userHasNote() this method validate how much notes have an user
     * @param int $user this is the id of the user in database.
     */
    private function userHasNote(){
        $this->postItList = [];
        if($this->dbtype === 'PDO'){            
            $stmt = $this->db->prepare('SELECT * from post_it WHERE user = :id');
            $stmt->bindValue(':id', $this->user, PDO::PARAM_INT);
            $stmt->execute();
            $this->postItList = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        if($this->dbtype === 'mysqli'){
            $stmt = $this->db->stmt_init();
            $stmt = $this->db->prepare('SELECT * from post_it WHERE user = ?');
            $stmt->bind_param('d', $this->user);
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
            $sql = file_get_contents('./createtable.sql');
            $this->db->query($sql);
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
    public function generatePostIt(bool $backrender){
        $html = '';
        if(!empty($this->postItList)){
            foreach($this->postItList as $k=>$post){
                $html.= $this->generateHTML($post, $k, $backrender);
            }
        }else{
            echo "<script> 
                window.addEventListener('DOMContentLoaded', ()=>{
                    window.location.reload()
                })
            </script>";
        }
        if($html!='')return $html;
    }

    /**
     * @method void insertNote() once you have the database created you need a note, for this reason this method makes you your first note
     * where you can write, resize it, move it or... make more notes!!!
     */
    public function insertNote(){
        $note = new PostIt(0, "This is a title!!",  "Hello! I\'m a cute note 😂", $this->user, 300, 0, 0, count($this->postItList)+1);
        if($this->dbtype === 'PDO'){
            $stmt = $this->db->prepare('INSERT INTO post_it (header, innertext, styles, user) VALUES (:header , :innertext, :styles , :user)');
            foreach($note->getAll() as $key=>$value){
                if($key!='id'){
                    $stmt->bindValue(':'.$key, $value, $this->pdoMap[$key]);
                }
            }
            $stmt->execute();
        }
        if($this->dbtype === 'mysqli'){
            $stmt = $this->db->stmt_init();
            $stmt = $this->db->prepare('INSERT INTO post_it (header, innertext, user, styles) VALUES (?,?,?,?)');
            $params = $note->getAll();
            $stmt->bind_param($this->mysqlMapCalculator($params), $params['header'], $params['innertext'], $params['user'], $params['styles']);
            $stmt->execute();
        }
        $this->userHasNote();
    }

    /**
     * @method string mysqliMapCalculator a way to get the correct bind params for inserts, and updates
     */
    private function mysqlMapCalculator(array $params):string{
        $map_return = '';
        foreach(array_keys($params) as $map){
            if($map!='id'){
                $map_return.=$this->mysqlMap[$map];
            }
        }
        return $map_return;
    }

    /**
     *  @method void generateHTML() the template
     */
    public function generateHTML($postIt, int $k, bool $backRender){
        $note = ($k!=0)?'<p class="delete"><img src="./bin.png"></p>':'';
        $html = 
        '<div class="post-it" data-id="'.$postIt->id.'" data-user="'.$postIt->user.'" style="'.$postIt->styles.'">
            <div class="post-it-window">
                '.$note.'
                <p class="add"><img src="./add.png"></p>
                <p class="minimize"><img src="./minimize.png"></p>
                <p class="close"><img src="./close.png"></p>
            </div>
            <div class="child-node">
                <h4 contenteditable="true"> '.$postIt->header.' </h4>
                <p contenteditable="true"> '.$postIt->innertext.' </p>
            </div>
        </div>';
        if($backRender){
            echo $html;
        }else{
            return $html;
        }
    }
    /**
     *  @method void updateNote() a method that allows you modify the notes
     */
    public function updateNote(array $params):void{
        [$id_fields, $update_field] = array_keys($params);
        if($this->dbtype === 'PDO' && $this->validateNote($params['id'])){
            $stmt = $this->db->prepare('UPDATE post_it SET '.$update_field.' = :'.$update_field.' where id = :id ');
            $stmt->bindValue(':id', $params['id'], PDO::PARAM_INT);
            $stmt->bindValue(':'.$update_field, $params[$update_field]);
            $stmt->execute();
        }
        if($this->dbtype === 'mysqli' && $this->validateNote($params['id'])){
            $stmt = $this->db->stmt_init();
            $stmt = $this->db->prepare('UPDATE post_it SET '.$update_field.' = ? where id = ? ');
            $stmt->bind_param('sd', $params[$update_field], $params['id']);            
            $stmt->execute();
        }
        $this->userHasNote();
        
    }
    public function deleteNote(int $id_note):void{
        if($this->dbtype === 'PDO' && $this->validateNote($id_note)){
            $stmt = $this->db->prepare('DELETE FROM post_it where id = :id ');
            $stmt->bindValue(':id', $id_note, PDO::PARAM_INT);
            $stmt->execute();
        }
        if($this->dbtype === 'mysqli' && $this->validateNote($id_note)){
            $stmt = $this->db->stmt_init();
            $stmt = $this->db->prepare('DELETE FROM post_it where id = ? ');
            $stmt->bind_param('s', $id_note);
            $stmt->execute();
        }
        $this->userHasNote();
    }

    private function validateNote(int $id_note):bool {
        return count(array_filter($this->postItList, fn($e)=>$e->id == $id_note))>0;
    }

    /**
     * @method void jsonMode() a convenient way to get all notes of an user in a json
     */
    public function jsonMode():string{
        return json_encode($this->postItList);
    }

    /**
     * @method void debugger a convinient way to debug php problems
     */
    public function debugger(array $params){
        echo '<pre>'; print_r($params); echo '</pre>';
    }

}



?>