<?php

namespace App\Modules\CountryKpa\Repository;

use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;
use App\Modules\CountryKpa\Domain\CountryKpa;
use Illuminate\Database\Eloquent\Model;
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

	public function create(array $data): object
	{
		try {
			return $this->model->create($data);
		} catch (\Exception $e) {
			throw new RuntimeException('Error al crear CountryKpa: ' . $e->getMessage());
		}
	}

	


}

