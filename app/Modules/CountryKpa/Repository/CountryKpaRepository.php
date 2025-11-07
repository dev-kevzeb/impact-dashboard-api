<?php

namespace App\Modules\CountryKpa\Repository;

use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use App\Modules\CountryKpa\Domain\CountryKpa;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Type\Decimal;
use RuntimeException;

class CountryKpaRepository extends Model
{

    protected CountryKpa $model;
    public function __construct(CountryKpa $model)
    {
        $this->model = $model;
    }

	public function getAll()
	{
		return $this->model->with(['country','kpa'])->get()->makeHidden(['id_country','id_kpa'])->toArray();
	}

	public function getById(int $id)
	{
		$countryKpa = $this->model->with(['country','kpa'])->find($id);
		
		if (!$countryKpa) {
			throw new RuntimeException("No se encontró CountryKpa con ID: {$id}");
		}
		
		return $countryKpa->makeHidden(['id_country','id_kpa']);
	}
	public function getCountryKpasByCountryId(int $countryId)
	{
		$countryKpas = $this->model->with(['country', 'kpa'])
        ->where('id_country', $countryId)
        ->get();

    if ($countryKpas->isEmpty()) {
        throw new \RuntimeException("No se encontró el país con ID: {$countryId}");
    }
    $country = $countryKpas->first()->country;

    $kpas = $countryKpas->map(function ($item) {
        return [
            'name' => $item->kpa->name,
            'implementation' => floatval($item->kpa->implementation),
        ];
    })->unique('name')->values()->all();

    return [
        'country' => [
            'id' => $country->id,
            'name' => $country->name,
            'currency_id' => $country->currency_id,
        ],
        'kpas' => $kpas,
    ];
	}
	public function create(array $data): object
	{
		try {
			// echo "Entrada: " . json_encode($data);			
			$id_country = $data['id_country'];
			$id_kpa = $data['id_kpa'];	
			$existing = $this->model->where('id_country', $id_country)
				->where('id_kpa', $id_kpa)
				->first();
			// echo "salida: " . json_encode($existing);
			if($existing){	
				throw new RuntimeException("La relación CountryKpa ya existe para id_country: {$id_country} e id_kpa: {$id_kpa}");
			}else{
				echo "No existing CountryKpa found. Proceeding to create.\n";	
			}

			return $this->model->create($data);
			
		} catch (\Exception $e) {
			throw new RuntimeException('Error al crear CountryKpa: ' . $e->getMessage());
		}
	}

	public function updateCountryKpa(int $id, array $data): object
	{
		try {
			$countryKpa = $this->model->findOrFail($id);
			
			$id_country = $data['id_country'];
			$id_kpa = $data['id_kpa'];
			
			// Verificar que no exista otra relación con el mismo country_id y kpa_id (excepto el registro actual)
			$existing = $this->model->where('id_country', $id_country)
				->where('id_kpa', $id_kpa)
				->where('id', '!=', $id)
				->first();
			
			if ($existing) {
				throw new RuntimeException("La relación CountryKpa ya existe para id_country: {$id_country} e id_kpa: {$id_kpa}");
			}
			
			$countryKpa->update($data);
			return $countryKpa->fresh();
			
		} catch (\Exception $e) {
			throw new RuntimeException('Error al actualizar CountryKpa: ' . $e->getMessage());
		}
	}


}


