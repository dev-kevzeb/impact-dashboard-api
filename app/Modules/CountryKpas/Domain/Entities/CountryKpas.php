<?php

namespace App\Modules\CountryKpas\Domain\Entities;

class CountryKpas
{
    private ?int $id;
    private int $countryId;
    private int $kpaId;

    public function __construct(?int $id, int $countryId, int $kpaId)
    {
        $this->id = $id;
        $this->countryId = $countryId;
        $this->kpaId = $kpaId;
    }

    public static function fromArray(array $data): self
    {
        return new self($data['id'] ?? null, (int)$data['id_country'], (int)$data['id_kpa']);
    }

    public function toArray(): array
    {
        return ['id' => $this->id, 'id_country' => $this->countryId, 'id_kpa' => $this->kpaId];
    }

    public function getId(): ?int { return $this->id; }
    public function getCountryId(): int { return $this->countryId; }
    public function getKpaId(): int { return $this->kpaId; }
}
