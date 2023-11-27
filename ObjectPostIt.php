<?php

class PostIt {
    private int $id;
    private string $header;
    private string $text;
    private int $user;
    private int $x;
    private int $y;
    private int $size;
    
    public function __construct($id, $header, $text, $user, $size, $x, $y){
        $this->id = $id??0;
        $this->header = $header;
        $this->text = $text;
        $this->user = $user;
        $this->size = $size;
        $this->x = $x;
        $this->y = $y;
    }

    public function getY(): int {
        return $this->y;
    }
    public function getX(){
        return $this->x;   
    }
    public function getId(): int {
        return $this->id;
    }

    public function getheader(): string {
        return $this->header;
    }

    public function getText(): string {
        return $this->text;
    }

    public function getUser(): int {
        return $this->user;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function set_x (int $x){
        $this->x = $x;
    }

    public function set_y(int $y){
        $this->y = $y;
    }

    public function set_header(string $header) :void{
        $this->header = $header;
    }

    public function set_innertext(string $text) :void{
        $this->text = $text;
    }
    public function set_user(int $user) :void{
        $this->user = $user;
    }

    public function __toString(){
        return json_encode($this);
    }

    public function getAll(){
        return [
            'id' => $this->id,
            'header'=> $this->header,
            'innertext'=> $this->text,
            'user' => $this->user,
            'size'=> $this->size, 
            'x' => $this->x,
            'y' => $this->y
        ];
    }

}

?>