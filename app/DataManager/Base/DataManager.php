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
     * @param $id
     * @param array $columns
     * @return Builder|Builder[]|Collection|Model
     */
    public function find($id, array $columns = ['*'])
    {
        $collection = $this->newQuery()->findOrFail($id, $columns);
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
     * @param bool $orWhere
     * @param bool $notWhere
     * @return Builder
     */
    protected function addCriteriaConditions(
        Builder $query,
        array $criteria,
        bool $orWhere = false,
        bool $notWhere = false
    ): Builder {
        $where = $this->whereType($orWhere);
        $whereIn = $this->whereInType($orWhere, $notWhere);
        $whereNull = $this->whereNullType($orWhere, $notWhere);

        foreach ($criteria as $key => $value) {
            $method = explode('.', $key)[0];
            if (method_exists($this->model, $method)) {
                if (is_array($value)) {
                    $query->whereHas(
                        $key,
                        function (Builder $query) use ($value, $orWhere) {
                            $this->addCriteriaConditions($query, $value, $orWhere);
                        }
                    );
                }
                continue;
            }

            if (strtolower($key) === 'or') {
                $query->where(
                    function (Builder $query) use ($value, $notWhere) {
                        $this->addCriteriaConditions($query, $value, true, $notWhere);
                    }
                );
                continue;
            }

            if (strtolower($key) === 'not') {
                $query->where(
                    function (Builder $query) use ($value, $orWhere) {
                        $this->addCriteriaConditions($query, $value, $orWhere, true);
                    }
                );
                continue;
            }

            if (is_array($value)) {
                if ($value === array_values($value)) {
                    if (in_array(null, $value)) {
                        $query->$where(
                            function (Builder $query) use ($key, $value) {
                                $query->whereIn($key, $value);
                                $query->orWhereNull($key);
                            }
                        );
                        continue;
                    }

                    $query->$whereIn($key, $value);
                    continue;
                }

                foreach ($value as $operator => $v) {
                    $query->$where($key, $operator, $v);
                }
                continue;
            } elseif (is_null($value)) {
                $query->$whereNull($key);
                continue;
            }

            $query->$where($key, $value);
        }

        return $query;
    }

    /**
     * @param $orWhere
     * @return string
     */
    private function whereType(bool $orWhere): string
    {
        if ($orWhere) {
            return 'orWhere';
        }
        return 'where';
    }

    /**
     * @param bool $orWhere
     * @param bool $notWhere
     * @return string
     */
    private function whereInType(bool $orWhere, bool $notWhere): string
    {
        if ($orWhere && $notWhere) {
            return 'orWhereNotIn';
        } elseif ($orWhere) {
            return 'orWhereIn';
        } elseif ($notWhere) {
            return 'whereNotIn';
        }

        return 'whereIn';
    }

    /**
     * @param bool $orWhere
     * @param bool $notWhere
     * @return string
     */
    private function whereNullType(bool $orWhere, bool $notWhere): string
    {
        if ($orWhere && $notWhere) {
            return 'orWhereNotNull';
        } elseif ($orWhere) {
            return 'orWhereNull';
        } elseif ($notWhere) {
            return 'whereNotNull';
        }
        return 'whereNull';
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
     * @param $id
     * @return int
     */
    public function update(array $data, $id): int
    {
        $record = $this->find($id);
        $updated = $record->update($data);
        $this->clearQuery();

        return $updated;
    }

    /**
     * Remove record from the database
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $record = $this->find($id);
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
