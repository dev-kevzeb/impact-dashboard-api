<?php

namespace App\Modules\Agency\Service;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Agency\Repository\AgencyRepository;
use RuntimeException;

class AgencyService
{
    private AgencyRepository $agencyRepository;

    public function __construct(AgencyRepository $agencyRepository)
    {
        $this->agencyRepository = $agencyRepository;
    }

    public function createAgency(string $name, string $url, bool $isApproved): Agency
    {
        if ($this->agencyRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe una agencia con el nombre: {$name}");
        }

        $agency = Agency::at($name, $url, $isApproved);
        
        $this->agencyRepository->save($agency);
        
        return $agency;
    }

    public function getAgencyById(int $id): Agency
    {
        return $this->agencyRepository->findById($id);
    }

    public function findAgencyByName(string $name): Agency
    {
        return $this->agencyRepository->findBy('name', $name);
    }

    public function getAllAgencies()
    {
        return $this->agencyRepository->getAll();
    }

    public function updateAgency(int $id, string $name, string $url, bool $isApproved): Agency
    {
        $agency = $this->agencyRepository->findById($id);

        try {
            $existingAgency = $this->agencyRepository->findBy('name', trim($name));
            if ($existingAgency && $existingAgency->id !== $id) {
                throw new RuntimeException("Ya existe otra agencia con el nombre: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
                throw $e; 
            }
        }

        $updatedAgency = Agency::at($name, $url, $isApproved);
        $agency->name = $updatedAgency->name;
        $agency->url = $updatedAgency->url;
        $agency->is_approved = $updatedAgency->is_approved;
        
        $this->agencyRepository->save($agency);
        
        return $agency;
    }

    public function agencyExists(string $name): bool
    {
        return $this->agencyRepository->exists('name', $name);
    }
}
