<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Agency extends Model
{
    // Constantes para mensajes de validación
    public const ERROR_NAME_EMPTY = 'el nombre de la agencia no debe ir vacio';
    public const ERROR_NAME_TOO_SHORT = 'el nombre de la agencia debe tener al menos 2 caracteres';
    public const ERROR_NAME_TOO_LONG = 'el nombre de la agencia no debe exceder 100 caracteres';
    public const ERROR_URL_EMPTY = 'la URL de la agencia no debe ir vacia';
    public const ERROR_URL_INVALID_FORMAT = 'la URL de la agencia debe tener un formato válido';
    public const ERROR_URL_INVALID_PROTOCOL = 'la URL de la agencia debe usar protocolo HTTP o HTTPS';
    public const ERROR_APPROVED_NOT_BOOLEAN = 'el estado de aprobación debe ser un valor booleano';

    private string $name;
    private string $url;
    private bool $isApproved;

    public function __construct(string $name, string $url, bool $isApproved)
    {
        $this->name = $name;
        $this->url = $url;
        $this->isApproved = $isApproved;
    }

    public static function at(string $name, string $url, mixed $isApproved): Agency
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::ERROR_NAME_EMPTY);
        }

        $trimmedName = trim($name);

        if (strlen($trimmedName) < 2) {
            throw new RuntimeException(self::ERROR_NAME_TOO_SHORT);
        }

        if (strlen($trimmedName) > 100) {
            throw new RuntimeException(self::ERROR_NAME_TOO_LONG);
        }

        // Validar URL (obligatoria)
        if (empty(trim($url))) {
            throw new RuntimeException(self::ERROR_URL_EMPTY);
        }

        $trimmedUrl = trim($url);

        if (!filter_var($trimmedUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException(self::ERROR_URL_INVALID_FORMAT);
        }

        $parsedUrl = parse_url($trimmedUrl);
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            throw new RuntimeException(self::ERROR_URL_INVALID_PROTOCOL);
        }

        // Validar isApproved
        if (!is_bool($isApproved)) {
            throw new RuntimeException(self::ERROR_APPROVED_NOT_BOOLEAN);
        }

        return new Agency($trimmedName, $trimmedUrl, $isApproved);
    }

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

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function compareIsApproved(bool $isApproved): bool
    {
        return $this->isApproved === $isApproved;
    }


}
