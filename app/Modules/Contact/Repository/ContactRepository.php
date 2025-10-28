<?php
namespace App\Modules\Contact\Repository;
use App\Modules\Contact\Domain\Contact;
use App\Repositories\AbstractRepository;
use App\Repositories\RepositoryInterface;

class ContactRepository extends AbstractRepository implements RepositoryInterface{
    public function __construct(Contact $model)
    {
        parent::__construct($model);
    }
}