<?php
namespace LaraService\Services;

use LaraRepo\Contracts\RepositoryInterface;
use LaraRepo\Criteria\Order\SortCriteria;
use LaraRepo\Criteria\Search\SearchCriteria;
use LaraRepo\Criteria\Where\WhereCriteria;
use LaraCrud\Repository\Contract\LaraRepositoryInterface;
use Illuminate\Support\Facades\Input;

class LaraService
{
    /**
     * @var
     */
    protected $baseRepository;

    /**
     * @var
     */
    protected $baseValidator;

    /**
     * last validation errors
     * @var array
     */
    protected $validationErrors;

    /**
     * @param RepositoryInterface $repository
     */
    public function setBaseRepository(RepositoryInterface $repository)
    {
        $this->baseRepository = $repository;
    }

    /**
     * @return mixed
     */
    public function getBaseRepository()
    {
        return $this->baseRepository;
    }

    /**
     * @param $validator
     */
    public function setBaseValidator($validator)
    {
        $this->baseValidator = $validator;
    }

    /**
     * @return mixed
     */
    public function getBaseValidator ()
    {
        return $this->baseValidator;
    }
    /**
     * @param $errors
     */
    public function setValidationErrors($errors)
    {
        $this->validationErrors = $errors;
    }

    /**
     * @return mixed returns the validation errors
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @param array $sort
     * @param string $group
     * @return array
     */
    public function paginate($sort = [], $group = 'list')
    {
        if (!empty($sort)) {
            $this->baseRepository->pushCriteria(new SortCriteria($sort));
        }

        return $this->paginateRepository($this->baseRepository, $group);
    }

        /**
     * @param $data
     * @return bool
     */
    public function create($data)
    {
        return $this->createWithRelations($data);
    }

    /**
     * @param $data
     * @param string $relations
     * @return bool
     */
    public function createWithRelations($data, $relations = '')
    {
        if ($this->validate($this->baseValidator, $data)) {

            $relations = $this->getRelationForSaveAssociated($relations );

            return $this->baseRepository->saveAssociated($data, $relations);

        }

        return false;
    }

    /**
    * @param $id
    * @param array $columns
    * @return mixed
    */
    public function findForShow($id, $columns = [])
    {
        return $this->baseRepository->findForShow($id, $columns);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->baseRepository->findFillable($id);
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     */
    public function update($id, $data)
    {
        return $this->updateWithRelations($id, $data);
    }

    /**
     * @param $id
     * @param $data
     * @param string $relations
     * @return bool
     */
    public function updateWithRelations($id, $data, $relations = '')
    {
        $data[$this->baseRepository->getKeyName()] = $id;
        if ($this->validate($this->baseValidator, $data)) {
            $model = $this->baseRepository->findFillable($id);
            $relations = $this->getRelationForSaveAssociated($relations);
            return $this->baseRepository->saveAssociated($data, $relations, $model);
        }

        return false;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->baseRepository->destroy($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->destroy($id);
    }


    /**
     * @param $validator
     * @param $data
     * @param array $options
     * @return bool
     */
    public function validate($validator, $data, $options = [])
    {
        if ($validator->isValid($data, $options)) {
            return true;
        }

        $this->setValidationErrors($validator->getErrors());
        return false;
    }

    /**
     * @param LaraRepositoryInterface $repository
     * @param string $group
     * @param null $column
     * @param null $val
     * @return array
     */
    protected function paginateRepositoryWhere(LaraRepositoryInterface $repository, $group = \ConstIndexableGroup::_List, $column = null, $val = null) {
        if (!empty($column) && !empty($val)) {
            $repository->pushCriteria(new WhereCriteria($column, $val, '='));
        }
        return $this->paginateRepository($repository, $group);
    }

    /**
     * @param RepositoryInterface $repository
     * @param string $group
     * @return array
     */
    protected function paginateRepository(RepositoryInterface $repository, $group = 'list')
    {
        $columns = $repository->getIndexableColumns(true, false, $group);
        $this->setSortingOptions($repository, [], $group);
        $items = $repository->paginate(20, [], $group);

        return [
            $items,
            $columns
        ];
    }

    /**
     * @param LaraRepositoryInterface $repository
     * @param array $options
     * @param string $group
     */
    protected function setSortingOptions(RepositoryInterface $repository, $options = [], $group = \ConstIndexableGroup::_List)
    {
        if (empty($options)) {
            // @TODO - request data in service ????
            $options = app('request')->request->all();
        }

        if (isset($options['column']) && isset($options['order'])) {
            $repository->setSortingOptions($options['column'], $options['order'], $group);
        }
    }


    /**
     * @param array $data
     * @return array
     */
    protected function getRelationForSaveAssociated($data = [])
    {
        if(is_string($data) && trim($data)) {
            $data = [$data];
        }

        if(!empty($data)) {
            return ['associated' => $data];
        }

        return [];
    }
//
//    /**
//     * @param $name
//     * @param $arguments
//     * @return mixed
//     * @throws \Exception
//     */
//    public function __call($name, $arguments)
//    {
//        if (method_exists($this->baseRepository, $name)) {
//            return $this->baseRepository->{$name}(...$arguments);
//        } else {
//            throw new \Exception(sprintf('This %s method does not exist in %s class', $name, get_class($this)));
//        }
//    }

}
