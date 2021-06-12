<?php

namespace App\DataManager\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface DataManagerInterface
 * @package App\DataManager\Base
 */
interface DataManagerInterface
{
    /**
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*']);

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, array $columns = ['*']);

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param array $columns
     * @return mixed
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null,
        array $columns = ['*']
    );

    /**
     * @param array $searchCriteria
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param array $columns
     * @return Collection|static[]
     */
    public function searchBy(
        array $searchCriteria = [],
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null,
        array $columns = ['*']
    );

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $relations
     * @return mixed
     */
    public function with($relations);

    /**
     * @return mixed
     */
    public function getModel();

    /**
     * @param Model $model
     * @return mixed
     */
    public function setModel(Model $model);
}
