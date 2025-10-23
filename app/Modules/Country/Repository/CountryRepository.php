<?php 

namespace App\Modules\Country\Repository;
use App\Modules\Country\Domain\Country;
// backup: original file saved as CountryRepository.php.bak
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
}