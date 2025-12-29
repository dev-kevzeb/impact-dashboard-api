<?php

namespace App\Modules\Donor\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use \App\Modules\Project\Domain\Project;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Donor extends Model
{
    use HasFactory;
    
    protected $table = 'donor';
    protected $fillable = ['name'];

    public static $ERROR_NAME_EMPTY = 'The donor name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'The donor name must have at least 2 characters';

    protected static function newFactory()
    {
        return \Database\Factories\DonorFactory::new();
    }
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at($name): Donor  
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen(trim($name)) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        
        return new Donor(['name' => trim($name)]);
    }
    
    public function validateName(): bool
    {
        return strlen($this->name) >= 2;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}