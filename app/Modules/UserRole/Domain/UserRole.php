<?php

namespace App\Modules\UserRole\Domain;

use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_role';
    protected $fillable = ['user_id', 'role_id'];

    // Timestamps enabled by default for audit trail
    public $timestamps = true;

    /**
     * Relationship: UserRole belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: UserRole belongs to a Role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Laravel Factory integration
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserRoleFactory::new();
    }
}
