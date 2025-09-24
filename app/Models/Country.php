<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Country extends Model
{
    private $name;
      static $INVALIDNAME = "el nombre del pais no debe ir vacio o tener menos de 3 caracteres";
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public static function at($name):Country{
        if($name == NULL){
            throw new RuntimeException('el nombre del pais no debe ser null');
        }
        if(strlen($name) == 0){
            throw new RuntimeException('el nombre del pais no debe ir vacio');
        }
 
        return new Country($name);
    }

    public function validateName():bool{
        return strlen($this->name) > 1;
    }


    public function getName():string{
        return $this->name;
    }
}
