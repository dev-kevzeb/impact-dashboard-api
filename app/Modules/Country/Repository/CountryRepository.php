<?php 

namespace App\Modules\Country\Repository;
use App\Modules\Country\Domain\Country;
class CountryRepository {

    protected Country $model;
    public function __construct(Country $model)
    {
        $this->model = $model;
    }

    public function getAll(){
            $country = $this->model->with('currency')->get()->makeHidden(['currency_id']);
            return $country;
        }
    public function getById(int $id){

        return $this->model->with('currency')->find($id)->makeHidden(['currency_id']);
    }
    public function create(array $data){
        return $this->model->create($data);
    }
    public function update(int $id, array $data){
        $country = $this->model->find($id);
        if($country){
            $country->update($data);
            return $country;
        }
        throw new \Exception('Country not found');
    }
    public function delete($id)
    {
        $country = Country::find($id);
        if ($country) {
            $country->delete();
        }
        return $country;
    }

    /**
     * Buscar país por nombre (case-insensitive)
     */
    public function findByName(string $name): ?Country
    {
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [trim($name)])->first();
    }

    /**
     * Verificar si existe un país con ese nombre
     */
    public function existsByName(string $name): bool
    {
        return $this->model->whereRaw('LOWER(name) = LOWER(?)', [trim($name)])->exists();
    }

    /**
     * Guardar una entidad Country
     */
    public function save(Country $country): void
    {
        $country->save();
    }
}