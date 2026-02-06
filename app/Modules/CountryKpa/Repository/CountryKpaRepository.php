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

	public function getByCountryAndKpa(int $countryId, int $kpaId)
	{
		return $this->model->where('id_country', $countryId)->where('id_kpa', $kpaId)->get();
	}

	public function getByCountry(int $countryId)
	{
		return $this->model->where('id_country', $countryId)->get();
	}


	public function getById(int $id)
	{
		$countryKpa = $this->model->with(['country','kpa'])->find($id);
		
		if (!$countryKpa) throw new RuntimeException("CountryKpa with ID not found:{$id}");
		return $countryKpa->makeHidden(['id_country','id_kpa']);
	}
	public function getCountryKpasByCountryId(int $countryId)
	{
		$countryKpas = $this->model->with(['country', 'kpa'])
        ->where('id_country', $countryId)->get();

		if ($countryKpas->isEmpty()) throw new RuntimeException("Country with ID not found: {$countryId}");
    	
		$country = $countryKpas->first()->country;

    	$kpas = $countryKpas->map(function ($item) {
			return [
				'id_ck' => $item->id,
				'id_kpa' => $item->kpa->id,
				'name' => $item->kpa->name,
				'implementation' => floatval($item->kpa->implementation),
				'strategic_outputs_count' => $item->strategic_outputs_count,
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

	public function getCountryKpasByCountryAndKpaId(int $countryId, int $kpaId)
	{
		$countryKpas = $this->model->with(['country', 'kpa'])
        ->where('id_country', $countryId)->where('id_kpa', $kpaId)->get();

		if ($countryKpas->isEmpty()) throw new RuntimeException("Country with ID not found: {$countryId}");
    	
		$country = $countryKpas->first()->country;

    	$kpas = $countryKpas->map(function ($item) {
			return [
				'id_ck' => $item->id,
				'id_kpa' => $item->kpa->id,
				'name' => $item->kpa->name,
				'implementation' => floatval($item->kpa->implementation),
				'strategic_outputs_count' => $item->strategic_outputs_count,
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
			$id_country = $data['id_country'];
			$id_kpa = $data['id_kpa'];	
			$existing = $this->model->where('id_country', $id_country)
				->where('id_kpa', $id_kpa)
				->first();
			if($existing){	
				throw new RuntimeException("The CountryKpa relationship already exists for id_country: {$id_country} and id_kpa: {$id_kpa}");
			}

			return $this->model->create($data);
			
		} catch (\Exception $e) {
			throw new RuntimeException('Error creating CountryKpa: ' . $e->getMessage());
		}
	}

	public function updateCountryKpa(int $id, array $data): object
	{
		try {
			$countryKpa = $this->model->findOrFail($id);
			
			$id_country = $data['id_country'];
			$id_kpa = $data['id_kpa'];
			$existing = $this->model->where('id_country', $id_country)
				->where('id_kpa', $id_kpa)
				->where('id', '!=', $id)
				->first();
			
			if ($existing) {
				throw new RuntimeException("The CountryKpa relationship already exists for id_country: {$id_country} and id_kpa: {$id_kpa}");
			}
			
			$countryKpa->update($data);
			return $countryKpa->fresh();
			
		} catch (\Exception $e) {
			throw new RuntimeException('Error updating CountryKpa: ' . $e->getMessage());
		}
	}

	public function getIdsByKpaId(int $kpaId){
		return $this->model->where('id_kpa', $kpaId)->pluck('id');
	}

	public function paginateKpasByCountry(int $countryId, ?string $search, int $perPage) {
		return $this->model->where('id_country', $countryId)
			->whereHas('kpa', function ($q) use ($search) {
				$q->when($search, fn ($sq) =>
					$sq->where('name', 'ILIKE', "%{$search}%")
				);
			})
			->with(['kpa:id,name'])->paginate($perPage)
			->through(function ($item) {
				return ['id'   => $item->kpa->id, 'name' => $item->kpa->name,];
			});
	}

}


