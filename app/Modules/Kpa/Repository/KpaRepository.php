<?php 
namespace App\Modules\Kpa\Repository;
use App\Modules\Kpa\Domain\Kpa;
// backup: original file saved as KpaRepository.php.bak
class KpaRepository {
    protected Kpa $model;
    public function __construct(Kpa $model)
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
        $kpa = $this->model->find($id);
        if($kpa){
            return $kpa->update($data);
        }
        return null;
    }
    public function delete($id)
    {
        $kpa = Kpa::find($id);
        if ($kpa) {
            $kpa->delete();
        }
        return $kpa;
    }
}