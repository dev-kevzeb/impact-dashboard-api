<?php

namespace App\Repositories;

use App\Models\Donor;

interface DonorRepositoryContract
{
    public function save(Donor $donor): void;

    public function findByName(string $name): ?Donor;

    public function getAll(): array;

    public function delete(Donor $donor): bool;

    public function exists(string $name): bool;

    public function count(): int;
}