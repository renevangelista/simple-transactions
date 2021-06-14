<?php

namespace App\DataManager\Base;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DataManager
 * @package App\DataManager\Base
 */
class DataManager implements DataManagerInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var null
     */
    protected $query;

    /**
     * DataManager constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = null;
    }

    /**
     * @return Builder
     */
    protected function newQuery(): Builder
    {
        if ($this->query == null) {
            $this->query = app(get_class($this->model))->newQuery();
        }
        return $this->query;
    }

    protected function clearQuery()
    {
        $this->query = null;
    }

    /**
     * Get all instances of model
     * @param array $columns
     * @return Collection|static[]
     */
    public function all(array $columns = ['*'])
    {
        $collection = $this->newQuery()->get($columns);
        $this->clearQuery();

        return $collection;
    }

    /**
     * Show the record with the given id
     * @param $identifier
     * @param array $columns
     * @return Builder|Builder[]|Collection|Model
     */
    public function find($identifier, array $columns = ['*'])
    {
        $collection = $this->newQuery()->findOrFail($identifier, $columns);
        $this->clearQuery();

        return $collection;
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param array $columns
     * @return Collection|static[]
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null,
        array $columns = ['*']
    ) {
        $query = $this->newQuery();

        $query = $this->addCriteriaConditions($query, $criteria);

        foreach ($orderBy ?: [] as $sort => $order) {
            $query->orderBy($sort, $order);
        }

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        $collection = $query->get($columns);

        $this->clearQuery();

        return $collection;
    }

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
    ) {
        $query = $this->newQuery();

        $query = $this->addCriteriaConditions($query, $criteria);

        $query->where(
            function (Builder $q) use ($searchCriteria) {
                foreach ($searchCriteria as $key => $value) {
                    $q->orWhere($key, 'LIKE', '%' . $value . '%');
                }
            }
        );

        foreach ($orderBy ?: [] as $sort => $order) {
            $query->orderBy($sort, $order);
        }

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        $collection = $query->get($columns);

        $this->clearQuery();

        return $collection;
    }

    /**
     * @param Builder $query
     * @param array $criteria
     * @return Builder
     */
    protected function addCriteriaConditions(
        Builder $query,
        array $criteria
    ): Builder {
        $where = $this->whereType();

        foreach ($criteria as $key => $value) {
            $query->$where($key, $value);
        }

        return $query;
    }

    /**
     * @return string
     */
    private function whereType(): string
    {
        return 'where';
    }

    /**
     * create a new record in the database
     * @param array $data
     * @return Builder|Model
     */
    public function create(array $data)
    {
        $model = $this->newQuery()->create($data);
        $this->clearQuery();

        return $model;
    }

    /**
     * Update record in the database
     * @param array $data
     * @param $identifier
     * @return int
     */
    public function update(array $data, $identifier): int
    {
        $record = $this->find($identifier);
        $updated = $record->update($data);
        $this->clearQuery();

        return $updated;
    }

    /**
     * Remove record from the database
     * @param $identifier
     * @return mixed
     */
    public function delete($identifier)
    {
        try {
            $record = $this->find($identifier);
            $deleted = $record->delete();
            $this->clearQuery();

            return $deleted;
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Eager load database relationships
     * @param $relations
     * @return DataManager
     */
    public function with($relations): DataManager
    {
        $this->newQuery()->with($relations);
        return $this;
    }

    /**
     * Get the associated model
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set the associated model
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }
}
