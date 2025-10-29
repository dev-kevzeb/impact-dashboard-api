<?php 

namespace App\Modules\Currency\Repository;
use App\Modules\Currency\Domain\Currency as Currency;
use Exception;

class CurrencyRepository {
    protected Currency $model;
    public function __construct(Currency $model)
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
     public function delete($id)
    {
        $currency = Currency::find($id);
        if ($currency) {
            $currency->delete();
        }
        return $currency;
    }
    public function update(int $id, array $data){
        $currency = $this->model->find($id);
        if($currency){
            return $currency->update($data);
        }
        throw new Exception('Currency not found');
    }
}
