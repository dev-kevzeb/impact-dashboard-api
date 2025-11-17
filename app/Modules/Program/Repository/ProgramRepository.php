<?php

namespace App\Modules\Program\Repository;

use App\Repositories\AbstractRepository;
use App\Modules\Program\Domain\Program;

/**
 * Repository para Program
 * 
 * @extends AbstractRepository<Program>
 */
class ProgramRepository extends AbstractRepository
{
    /**
     * Constructor
     * 
     * @param Program $model Instancia del modelo Program
     */
    public function __construct(Program $model)
    {
        parent::__construct($model);
    }
    
    /**
     * Obtener programas con todas sus relaciones cargadas
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithRelations()
    {
        return $this->model
            ->with([
                'contact',
                'beneficiary',
                'programState',
                'country',
                'agency',
                'sdgs',
                'donors'
                // 'projects' // TODO: Descomentar cuando el módulo Project exista
            ])
            ->get();
    }
    
    /**
     * Buscar programa por ID con todas sus relaciones
     * 
     * @param int $id
     * @return Program
     * @throws \RuntimeException Si no se encuentra
     */
    public function findByIdWithRelations(int $id): Program
    {
        $program = $this->model
            ->with([
                'contact',
                'beneficiary',
                'programState',
                'country',
                'agency',
                'sdgs',
                'donors'
                // 'projects' // TODO: Descomentar cuando el módulo Project exista
            ])
            ->find($id);
            
        if (!$program) {
            throw new \RuntimeException("Programa con ID {$id} no encontrado");
        }
        
        return $program;
    }
    
    /**
     * Sincronizar SDGs del programa
     * 
     * @param Program $program
     * @param array $sdgIds Array de IDs de SDGs
     * @return void
     */
    public function syncSdgs(Program $program, array $sdgIds): void
    {
        $program->sdgs()->sync($sdgIds);
    }
    
    /**
     * Sincronizar Donors del programa
     * 
     * @param Program $program
     * @param array $donorIds Array de IDs de Donors
     * @return void
     */
    public function syncDonors(Program $program, array $donorIds): void
    {
        $program->donors()->sync($donorIds);
    }
    
    /**
     * Obtener programas por país
     * 
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByCountry(int $countryId)
    {
        return $this->model
            ->where('country_id', $countryId)
            ->with(['contact', 'beneficiary', 'programState', 'agency'])
            ->get();
    }
    
    /**
     * Obtener programas por agencia
     * 
     * @param int $agencyId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByAgency(int $agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->with(['contact', 'beneficiary', 'programState', 'country'])
            ->get();
    }
    
    /**
     * Obtener programas por estado
     * 
     * @param int $programStateId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByProgramState(int $programStateId)
    {
        return $this->model
            ->where('program_state_id', $programStateId)
            ->with(['contact', 'beneficiary', 'country', 'agency'])
            ->get();
    }
}
