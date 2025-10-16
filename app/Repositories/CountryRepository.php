<?php 

namespace App\Repositories;
use App\Models\Country;
class CountryRepository {

    protected Country $model;
    public function __construct(Country $model)
    {
        $this->model = $model;
    }

    public function getAll(){
        return $this->model->all();         
    }
    public function getById(int $id){
        return $this->model->find($id);
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
        return null;
    }
}