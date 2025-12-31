<?php

namespace App\Modules\ProgramState\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProgramState extends Model
{
    use HasFactory;

    protected $table = 'program_state';
    protected $fillable = ['name'];

    public static $ERROR_NAME_EMPTY = 'the state name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'the state name must be at least 2 characters';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $name): ProgramState
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }

        return new ProgramState(['name' => trim($name)]);
    }

    public function validateName(): bool
    {
        return strlen($this->name) >= 2;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Laravel Factory integration
     *
     * @return \Database\Factories\ProgramStateFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\ProgramStateFactory::new();
    }

    // ============================================
    // ELOQUENT RELATIONSHIPS
    // ============================================

    /**
     * 1:N relationship with Program
     * A state can have multiple programs
     */
    public function programs()
    {
        return $this->hasMany(\App\Modules\Program\Domain\Program::class, 'program_state_id');
    }
}
