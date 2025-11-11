<?php

namespace App\Modules\Program\Service;

use App\Modules\Program\Domain\Program;
use App\Modules\Program\Repository\ProgramRepository;
use App\Modules\Contact\Repository\ContactRepository;
use App\Modules\Beneficiary\Repository\BeneficiaryRepository;
use App\Modules\ProgramState\Repository\ProgramStateRepository;
use App\Modules\Country\Repository\CountryRepository;
use App\Modules\Agency\Repository\AgencyRepository;
use App\Modules\Sdg\Repository\SdgRepository;
use App\Modules\Donor\Repository\DonorRepository;
use RuntimeException;

class ProgramService
{
    private ProgramRepository $programRepository;
    private ContactRepository $contactRepository;
    private BeneficiaryRepository $beneficiaryRepository;
    private ProgramStateRepository $programStateRepository;
    private CountryRepository $countryRepository;
    private AgencyRepository $agencyRepository;
    private SdgRepository $sdgRepository;
    private DonorRepository $donorRepository;

    public function __construct(
        ProgramRepository $programRepository,
        ContactRepository $contactRepository,
        BeneficiaryRepository $beneficiaryRepository,
        ProgramStateRepository $programStateRepository,
        CountryRepository $countryRepository,
        AgencyRepository $agencyRepository,
        SdgRepository $sdgRepository,
        DonorRepository $donorRepository
    ) {
        $this->programRepository = $programRepository;
        $this->contactRepository = $contactRepository;
        $this->beneficiaryRepository = $beneficiaryRepository;
        $this->programStateRepository = $programStateRepository;
        $this->countryRepository = $countryRepository;
        $this->agencyRepository = $agencyRepository;
        $this->sdgRepository = $sdgRepository;
        $this->donorRepository = $donorRepository;
    }

    /**
     * Crear un nuevo programa
     */
    public function createProgram(
        string $name,
        string $description,
        string $bannerImg,
        string $startDate,
        string $endDate,
        string $programUrl,
        int $contactId,
        int $beneficiaryId,
        int $programStateId,
        int $countryId,
        int $agencyId,
        array $sdgIds = [],
        array $donorIds = []
    ): Program {
        // Validar que no exista programa con el mismo nombre
        if ($this->programRepository->exists('name', trim($name))) {
            throw new RuntimeException("Ya existe un programa con el nombre: {$name}");
        }

        // Validar que existan las entidades relacionadas
        $this->validateRelatedEntities(
            $contactId,
            $beneficiaryId,
            $programStateId,
            $countryId,
            $agencyId,
            $sdgIds,
            $donorIds
        );

        // Obtener objetos de las entidades relacionadas
        $contact = $this->contactRepository->findById($contactId);
        $beneficiary = $this->beneficiaryRepository->findById($beneficiaryId);
        $programState = $this->programStateRepository->findById($programStateId);
        $country = $this->countryRepository->findById($countryId);
        $agency = $this->agencyRepository->findById($agencyId);

        // Crear programa usando factory method con objetos
        $program = Program::at(
            $name,
            $description,
            $bannerImg,
            $startDate,
            $endDate,
            $programUrl,
            $contact,
            $beneficiary,
            $programState,
            $country,
            $agency
        );

        // Guardar en base de datos
        $this->programRepository->save($program);

        // Sincronizar relaciones M:N
        if (!empty($sdgIds)) {
            $this->programRepository->syncSdgs($program, $sdgIds);
        }
        if (!empty($donorIds)) {
            $this->programRepository->syncDonors($program, $donorIds);
        }

        return $program->fresh(['contact', 'beneficiary', 'programState', 'country', 'agency', 'sdgs', 'donors']);
    }

    /**
     * Obtener programa por ID
     */
    public function getProgramById(int $id): Program
    {
        return $this->programRepository->findByIdWithRelations($id);
    }

    /**
     * Obtener todos los programas
     */
    public function getAllPrograms()
    {
        return $this->programRepository->getAllWithRelations();
    }

    /**
     * Actualizar un programa
     */
    public function updateProgram(
        int $id,
        string $name,
        string $description,
        string $bannerImg,
        string $startDate,
        string $endDate,
        string $programUrl,
        int $contactId,
        int $beneficiaryId,
        int $programStateId,
        int $countryId,
        int $agencyId,
        array $sdgIds = [],
        array $donorIds = []
    ): Program {
        // Obtener programa existente
        $program = $this->programRepository->findById($id);

        // Validar duplicados (excepto el mismo programa)
        try {
            $existing = $this->programRepository->findBy('name', trim($name));
            if ($existing && $existing->id !== $id) {
                throw new RuntimeException("Ya existe otro programa con el nombre: {$name}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
                throw $e;
            }
        }

        // Validar entidades relacionadas
        $this->validateRelatedEntities(
            $contactId,
            $beneficiaryId,
            $programStateId,
            $countryId,
            $agencyId,
            $sdgIds,
            $donorIds
        );

        // Obtener objetos de las entidades relacionadas
        $contact = $this->contactRepository->findById($contactId);
        $beneficiary = $this->beneficiaryRepository->findById($beneficiaryId);
        $programState = $this->programStateRepository->findById($programStateId);
        $country = $this->countryRepository->findById($countryId);
        $agency = $this->agencyRepository->findById($agencyId);

        // Validar datos con factory method (sin guardar)
        Program::at(
            $name,
            $description,
            $bannerImg,
            $startDate,
            $endDate,
            $programUrl,
            $contact,
            $beneficiary,
            $programState,
            $country,
            $agency
        );

        // Actualizar
        $this->programRepository->update($program, [
            'name' => trim($name),
            'description' => trim($description),
            'banner_img' => trim($bannerImg),
            'start_date' => trim($startDate),
            'end_date' => trim($endDate),
            'program_url' => trim($programUrl),
            'contact_id' => $contactId,
            'beneficiary_id' => $beneficiaryId,
            'program_state_id' => $programStateId,
            'country_id' => $countryId,
            'agency_id' => $agencyId
        ]);

        // Sincronizar relaciones M:N
        $this->programRepository->syncSdgs($program, $sdgIds);
        $this->programRepository->syncDonors($program, $donorIds);

        return $program->fresh(['contact', 'beneficiary', 'programState', 'country', 'agency', 'sdgs', 'donors']);
    }

    /**
     * Buscar programa por nombre
     */
    public function findProgramByName(string $name): Program
    {
        return $this->programRepository->findBy('name', $name);
    }

    /**
     * Obtener programas por país
     */
    public function getProgramsByCountry(int $countryId)
    {
        // Validar que el país exista
        $this->countryRepository->findById($countryId);
        
        return $this->programRepository->findByCountry($countryId);
    }

    /**
     * Obtener programas por agencia
     */
    public function getProgramsByAgency(int $agencyId)
    {
        // Validar que la agencia exista
        $this->agencyRepository->findById($agencyId);
        
        return $this->programRepository->findByAgency($agencyId);
    }

    /**
     * Obtener programas por estado
     */
    public function getProgramsByState(int $programStateId)
    {
        // Validar que el estado exista
        $this->programStateRepository->findById($programStateId);
        
        return $this->programRepository->findByState($programStateId);
    }

    /**
     * Validar que existan todas las entidades relacionadas
     */
    private function validateRelatedEntities(
        int $contactId,
        int $beneficiaryId,
        int $programStateId,
        int $countryId,
        int $agencyId,
        array $sdgIds,
        array $donorIds
    ): void {
        // Validar Contact
        $this->contactRepository->findById($contactId);

        // Validar Beneficiary
        $this->beneficiaryRepository->findById($beneficiaryId);

        // Validar ProgramState
        $this->programStateRepository->findById($programStateId);

        // Validar Country
        $this->countryRepository->findById($countryId);

        // Validar Agency
        $this->agencyRepository->findById($agencyId);

        // Validar SDGs
        foreach ($sdgIds as $sdgId) {
            $this->sdgRepository->findById($sdgId);
        }

        // Validar Donors
        foreach ($donorIds as $donorId) {
            $this->donorRepository->findById($donorId);
        }
    }
}
