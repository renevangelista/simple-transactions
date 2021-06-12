<?php

namespace App\Service\Base;

use Exception;
use App\DataManager\Base\DataManager;
use App\Model\Core\Message;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PDOException;

/**
 * Class Service
 * @package App\Service\Base
 */
abstract class Service implements ServiceInterface
{
    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * Service constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->dataManager = new DataManager($model);
        $this->message = new Message();
        $this->relations = [];
    }

    /**
     * @param array $columns
     * @return Message
     */
    public function all(array $columns = ['*']): Message
    {
        $models = $this->dataManager->with($this->relations)->all($columns);
        $this->clearWith();

        if ($models) {
            return $this->message->success(trans('system.messages.success'), $models);
        }

        return $this->message->error(trans('system.messages.list_could_not_be_retrieved'), null, '');
    }

    /**
     * @param mixed $id
     * @param array $columns
     * @return Message
     */
    public function find($id, array $columns = ['*']): Message
    {
        $model = $this->dataManager->with($this->relations)->find($id, $columns);
        $this->clearWith();

        if ($model) {
            return $this->message->success(trans('system.messages.success'), $model);
        }

        return $this->message->error(trans('system.messages.item_could_not_be_retrieved'), null, '');
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param array $columns
     * @return Message
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null,
        array $columns = ['*']
    ): Message {
        $model = $this->dataManager->with($this->relations)->findBy($criteria, $orderBy, $limit, $offset, $columns);
        $this->clearWith();

        if ($model) {
            return $this->message->success(trans('system.messages.success'), $model);
        }

        return $this->message->error(trans('system.messages.list_could_not_be_retrieved'), null, '');
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param array $columns
     * @return Message
     *
     * @throws ModelNotFoundException
     */
    public function findOneBy(
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null,
        array $columns = ['*']
    ): Message {
        $message = $this->findBy($criteria, $orderBy, $limit, $offset, $columns);

        if ($message->isError()) {
            return $this->message->error(trans('system.messages.item_could_not_be_retrieved'), null, '');
        }

        /** @var Collection $collection */
        $collection = $message->getData();
        $model = $collection->first();

        if ($model == null) {
            throw (new ModelNotFoundException());
        }

        return $this->message->success($message->getMessage(), $model);
    }

    /**
     * @param array $searchCriteria
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param array $columns
     * @return Message
     */
    public function searchBy(
        array $searchCriteria = [],
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null,
        array $columns = ['*']
    ): Message {
        $model = $this->dataManager->with($this->relations)->searchBy(
            $searchCriteria,
            $criteria,
            $orderBy,
            $limit,
            $offset,
            $columns
        );
        $this->clearWith();

        if ($model) {
            return $this->message->success(trans('system.messages.success'), $model);
        }

        return $this->message->error(trans('system.messages.list_could_not_be_retrieved'), null, '');
    }

    /**
     * @param array $data
     * @return Message
     */
    public function create(array $data): Message
    {
        $message = $this->validate($data);
        if ($message->isError()) {
            return $message;
        }

        $dataModel = Arr::only($data, $this->dataManager->getModel()->getFillable());
        $model = $this->dataManager->create($dataModel);

        if ($model) {
            return $this->message->success(trans('system.messages.registered_successfully'), $model);
        }

        return $this->message->error(trans('system.messages.it_was_not_possible_register'), null, '');
    }

    /**
     * @param array $data
     * @param $id
     * @return Message
     */
    public function update(array $data, $id): Message
    {
        $message = $this->find($id);
        if ($message->isError()) {
            return $message;
        }

        $message = $this->validate($data, $id);
        if ($message->isError()) {
            return $message;
        }

        $dataModel = Arr::only($data, $this->dataManager->getModel()->getFillable());
        $model = $this->dataManager->update($dataModel, $id);

        if ($model) {
            return $this->message->success(trans('system.messages.updated_successfully'), $model);
        }

        return $this->message->error(trans('system.messages.it_was_not_possible_update'), null, '');
    }

    /**
     * @param mixed $id
     * @return Message
     */
    public function delete($id): Message
    {
        try {
            $message = $this->find($id);
            if ($message->isError()) {
                return $message;
            }

            $model = $this->dataManager->delete($id);

            if ($model) {
                return $this->message->success(trans('system.messages.successfully_deleted'), $model);
            }

            return $this->message->error(trans('system.messages.it_was_not_possible_delete'), null, '');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return $this->message->error(
                    trans('system.messages.it_is_not_possible_to_delete_objects_with_associations'),
                    null,
                    $exception->getMessage()
                );
            }
            return $this->message->error(
                trans('system.messages.it_was_not_possible_delete'),
                null,
                $exception->getMessage()
            );
        } catch (Exception $exception) {
            return $this->message->error(
                trans('system.messages.it_was_not_possible_delete'),
                null,
                $exception->getMessage()
            );
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 6) != 'findBy' && substr($name, 0, 9) != 'findOneBy') {
            throw new Exception(
                "Undefined method '$name'. The method name must start with either findBy or findOneBy!"
            );
        } elseif (substr($name, 0, 6) == 'findBy') {
            $by = substr($name, 6, strlen($name));
            $name = 'findBy';
        } elseif (substr($name, 0, 9) == 'findOneBy') {
            $by = substr($name, 9, strlen($name));
            $name = 'findOneBy';
        }

        $fieldName = lcfirst($by);

        if ($this->dataManager->getModel()->isFillable($fieldName) == false) {
            throw new Exception(
                "Undefined property '$fieldName'. $fieldName must be a fillable attribute of the " . get_class(
                    $this->dataManager->getModel()
                ) . "::class."
            );
        }

        return $this->$name([$fieldName => $arguments[0]]);
    }

    /**
     * @return Guard
     */
    public function guard(): Guard
    {
        return Auth::guard();
    }

    /**
     * @param array $data
     * @param int $id
     * @return Message
     */
    public function validate(array $data, $id = null): Message
    {
        $validator = Validator::make($data, $this->rules($id));
        if ($validator->fails()) {
            return $this->message->error(trans('system.messages.some_field_is_not_valid'), null, $validator->errors());
        }
        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function with(array $relations): Service
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * return void
     */
    public function clearWith()
    {
        $this->relations = [];
    }

    /**
     * @param mixed $id
     * @return array
     */
    abstract public function rules($id): array;
}
