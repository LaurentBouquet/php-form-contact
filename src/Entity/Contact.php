<?php
namespace App\Entity;

class Contact {

    public $name;
    public $email;
    public $gender;
    public $comment;
    public $website;
    public $navigation;
    public $quality;

    public function reset() {
        $this->name = "";
        $this->email = "";
        $this->gender = "";
        $this->comment = "";
        $this->website = "";
        $this->navigation = "";
        $this->quality = "";
    }
}