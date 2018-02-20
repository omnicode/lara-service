<?php

namespace LaraService\Services;

use LaraRepo\Contracts\RepositoryInterface;
use LaraRepo\Criteria\Order\SortCriteria;
use LaraRepo\Criteria\Where\WhereCriteria;

class LaraService
{
    const GROUP = 'list';

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
    public function paginate($sort = [], $group = self::GROUP)
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
     * @param null $relations
     * @return bool
     */
    public function createWithRelations($data, $relations = null)
    {
        if ($this->validate($this->baseValidator, $data)) {
            $relations = $this->getRelationForSaveAssociated($relations);
            return $this->baseRepository->saveAssociated($data, $relations);
        }

        return false;
    }

    /**
     * @param $id
     * @param null $columns
     * @return mixed
     */
    public function findForShow($id, $columns = null)
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
     * @param null $relations
     * @return bool
     */
    public function updateWithRelations($id, $data, $relations = null)
    {
        $data[$this->baseRepository->getKeyName()] = $id;

        if ($this->validate($this->baseValidator, $data)) {
            $model = $this->baseRepository->findFillable($id);

            if (empty($model)) {
                //TODO return false or throw exception
                return false;
            }

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
     * @param $repository
     * @param string $group
     * @param null $column
     * @param null $val
     * @return array
     */
    public function paginateRepositoryWhere($repository, $group = self::GROUP, $column = null, $val = null)
    {
        if (!empty($column) && !empty($val)) {
            $repository->pushCriteria(new WhereCriteria($column, $val, '='));
        }

        return $this->paginateRepository($repository, $group);
    }

    /**
     * @param $repository
     * @param string $group
     * @return array
     */
    public function paginateRepository($repository, $group = self::GROUP)
    {
        $columns = $repository->getIndexableColumns(true, false, $group);
        $this->setSortingOptions($repository, [], $group);
        $items = $repository->paginate(20, null, $group);

        return [
            $items,
            $columns
        ];
    }

    /**
     * @param $repository
     * @param array $options
     * @param $group
     */
    public function setSortingOptions($repository, $options = [], $group = self::GROUP)
    {
        if (empty($options)) {
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

}
