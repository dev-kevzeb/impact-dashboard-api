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
    protected $fillable = ['name', 'contribution', 'project_id'];
    
    public static $ERROR_NAME_EMPTY = 'el nombre del donante no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del donante debe tener al menos 2 caracteres';
    public static $ERROR_CONTRIBUTION_NOT_NUMERIC = 'la contribución debe ser un número';
    public static $ERROR_CONTRIBUTION_OUT_OF_RANGE = 'la contribución debe estar entre 0 y 100';
    public static $ERROR_PROJECT_INVALID = 'el proyecto asociado es inválido';
    

    protected static function newFactory()
    {
        return \Database\Factories\DonorFactory::new();
    }
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at($name, $contribution, $project): Donor  
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen(trim($name)) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);

        if(!is_numeric($contribution)) throw new RuntimeException(self::$ERROR_CONTRIBUTION_NOT_NUMERIC);
        $contributionFloat = (float) $contribution;
        if ($contributionFloat < 0 || $contributionFloat > 100) throw new RuntimeException(self::$ERROR_CONTRIBUTION_OUT_OF_RANGE);

        if(!($project instanceof Project)) throw new RuntimeException(self::$ERROR_PROJECT_INVALID);
        
        return new Donor(['name' => trim($name), 'contribution' => $contributionFloat, 'project_id' => $project->id]);
    }
    
    public function validateName(): bool
    {
        return strlen($this->name) >= 2;
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    // ============================================
    // RELACIONES ELOQUENT
    // ============================================

    /**
     * Relación M:N con Program
     * Un donante puede financiar múltiples programas
     */
    public function programs()
    {
        return $this->belongsToMany(\App\Modules\Program\Domain\Program::class, 'program_donor', 'donor_id', 'program_id');
    }
}