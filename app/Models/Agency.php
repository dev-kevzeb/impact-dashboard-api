<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Agency extends Model
{
    private $name;
    private $url;
    private $isApproved;
    // constructor 
    public function __construct($name, $url, $isApproved)
    {
        $this->name = $name;
        $this->url = $url;
        $this->isApproved = $isApproved;
    }
    public static function at($name): Agency
    {
        if(strlen($name)==0){
            throw new RuntimeException('el nombre de la agencia no debe ir vacio');
        }
        return new Agency($name, '', false);
    }


    // getters
    public function getName(): string
    {
        return $this->name;
    }
    public function getUrl(): string
    {
        return $this->url;
    }
    public function getIsApproved(): bool
    {
        return $this->isApproved;
    }
    public function compareIsApproved($isApproved):bool{

    // ("false" => false) , ("falso" => false) , (true => true) , (false => false)
        return $this->getIsApproved() === $isApproved;
    }
}
