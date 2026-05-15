<?php

namespace App\Modules\CountryJoinRequest\Domain;

use App\Modules\Country\Domain\Country;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryJoinRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVOKED = 'revoked';

    protected $table = 'country_join_request';

    protected $fillable = [
        'country_id',
        'requester_user_role_id',
        'status',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function requesterUserRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'requester_user_role_id');
    }
}
