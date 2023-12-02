<?php

class PostIt {
    private int $id;
    private string $header;
    private string $text;
    private int $user;
    private string $styles;
    
    public function __construct($id, $header, $text, $user, $size, $x, $y, $zindex){
        $this->id = $id??0;
        $this->header = $header;
        $this->text = $text;
        $this->user = $user;
        $this->styles = "z-index: ".(1000*$zindex)." ;width: $size; left:$x; top:$y;";
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
            'styles' => $this->styles
        ];
    }

}

?>