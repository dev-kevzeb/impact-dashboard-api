<?php

namespace App\Modules\CountryKpas\Repository;

use App\Modules\CountryKpas\Domain\Entities\CountryKpasModel as CountryKpasModel;
use App\Modules\CountryKpas\Domain\Entities\CountryKpas as CountryKpasEntity;

class CountryKpasRepository
{
	protected CountryKpasModel $model;

	public function __construct(CountryKpasModel $model)
	{
		$this->model = $model;
	}

	public function getAll()
	{
		return $this->model->all();
	}

	public function getById(int $id)
	{
		return $this->model->find($id);
	}

	public function create(array $data): CountryKpasModel
	{
		return $this->model->create($data);
	}

	public function delete(int $id)
	{
		$rec = $this->model->find($id);
		if ($rec) $rec->delete();
		return $rec;
	}

	public function attachKpaToCountry(int $countryId, int $kpaId)
	{
		return $this->model->create(['id_country' => $countryId, 'id_kpa' => $kpaId]);
	}

	public function detachKpaFromCountry(int $countryId, int $kpaId)
	{
		return $this->model->where('id_country', $countryId)->where('id_kpa', $kpaId)->delete();
	}
}

