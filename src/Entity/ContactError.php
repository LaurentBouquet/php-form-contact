<?php
namespace App\Entity;

class ContactError {

    public $nameErr;
    public $emailErr;
    public $genderErr;
    public $websiteErr;


    public function reset(){
        $this->nameErr = "";
        $this->emailErr = "";
        $this->genderErr = "";
        $this->websiteErr = "";
    }

}