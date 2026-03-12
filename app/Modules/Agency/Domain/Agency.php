<?php

namespace App\Modules\Agency\Domain;

use App\Modules\Project\Domain\Project;
use App\Modules\ProjectAgency\Domain\ProjectAgency;
use Database\Factories\AgencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Agency extends Model
{
    use HasFactory;
    protected $table = 'agency';
    protected $fillable = ['name', 'url', 'is_approved'];

    public static $ERROR_NAME_EMPTY = 'The agency name must not be empty';
    public static $ERROR_NAME_TOO_SHORT = 'The agency name must have at least 2 characters';
    public static $ERROR_NAME_TOO_LONG = 'The agency name must not exceed 100 characters';
    public static $ERROR_URL_INVALID_FORMAT = 'The agency URL must have a valid format';
    public static $ERROR_URL_INVALID_PROTOCOL = 'The agency URL must use HTTP or HTTPS protocol';
    public static $ERROR_APPROVED_NOT_BOOLEAN = 'The approval status must be a boolean value';


    public static function newFactory()
    {
        return AgencyFactory::new();
    }
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $name, string $url, mixed $isApproved): Agency
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);

        $trimmedName = trim($name);

        if (strlen($trimmedName) < 2) throw new RuntimeException(self::$ERROR_NAME_TOO_SHORT);
        if (strlen($trimmedName) > 100) throw new RuntimeException(self::$ERROR_NAME_TOO_LONG);

        $trimmedUrl = trim($url);
        if ($trimmedUrl !== '') {
            if (!filter_var($trimmedUrl, FILTER_VALIDATE_URL)) throw new RuntimeException(self::$ERROR_URL_INVALID_FORMAT);
            $parsedUrl = parse_url($trimmedUrl);
            if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'], true)) throw new RuntimeException(self::$ERROR_URL_INVALID_PROTOCOL);
        }

        if (!is_bool($isApproved)) throw new RuntimeException(self::$ERROR_APPROVED_NOT_BOOLEAN);

        return new Agency([
            'name' => $trimmedName,
            'url' => $trimmedUrl,
            'is_approved' => $isApproved
        ]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url ?? '';
    }

    public function isApproved(): bool
    {
        return (bool) $this->is_approved;
    }

    public function projectAgencies()
    {
        return $this->hasMany(ProjectAgency::class, 'agency_id', 'id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_agency', 'agency_id', 'project_id');
    }
}
