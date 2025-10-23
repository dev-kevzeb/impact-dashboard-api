<?php

namespace App\Modules\CountryKpa\Repository;

use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use App\Modules\CountryKpa\Domain\CountryKpa as CountryKpaModel;
use RuntimeException;

class CountryKpaRepository extends AbstractRepository implements RepositoryInterface
{
	public function __construct(CountryKpaModel $model)
	{
		parent::__construct($model);
	}

	public function getAll(): array
	{
		return parent::getAll();
	}

	public function getById(int $id): object
	{
		return parent::findById($id);
	}

	public function create(array $data): object
	{
		try {
			return $this->model->create($data);
		} catch (\Exception $e) {
			throw new RuntimeException('Error al crear CountryKpa: ' . $e->getMessage());
		}
	}

	public function delete(int $id): ?object
	{
		$rec = $this->model->find($id);
		if ($rec) {
			$rec->delete();
		}
		return $rec;
	}

	public function attachKpaToCountry(int $countryId, int $kpaId): object
	{
		return $this->create(['id_country' => $countryId, 'id_kpa' => $kpaId]);
	}

	public function detachKpaFromCountry(int $countryId, int $kpaId): int
	{
		return $this->model->where('id_country', $countryId)->where('id_kpa', $kpaId)->delete();
	}
}

