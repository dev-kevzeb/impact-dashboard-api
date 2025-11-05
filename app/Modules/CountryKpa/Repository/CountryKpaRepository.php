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
		return $this->model->with(['country','kpa'])->find($id)->makeHidden(['id_country','id_kpa']);

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
	


}

