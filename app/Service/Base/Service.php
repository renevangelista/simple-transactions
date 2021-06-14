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
     * @param mixed $identifier
     * @param array $columns
     * @return Message
     */
    public function find($identifier, array $columns = ['*']): Message
    {
        $model = $this->dataManager->with($this->relations)->find($identifier, $columns);
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
     * @param $identifier
     * @return Message
     */
    public function update(array $data, $identifier): Message
    {
        $message = $this->find($identifier);
        if ($message->isError()) {
            return $message;
        }

        $message = $this->validate($data, $identifier);
        if ($message->isError()) {
            return $message;
        }

        $dataModel = Arr::only($data, $this->dataManager->getModel()->getFillable());
        $model = $this->dataManager->update($dataModel, $identifier);

        if ($model) {
            return $this->message->success(trans('system.messages.updated_successfully'), $model);
        }

        return $this->message->error(trans('system.messages.it_was_not_possible_update'), null, '');
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
            $findBy = substr($name, 6, strlen($name));
            $name = 'findBy';
        } elseif (substr($name, 0, 9) == 'findOneBy') {
            $findBy = substr($name, 9, strlen($name));
            $name = 'findOneBy';
        }

        $fieldName = lcfirst($findBy);

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
     * @param null $identifier
     * @return Message
     */
    public function validate(array $data, $identifier = null): Message
    {
        $validator = Validator::make($data, $this->rules($identifier));
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
     * @param $identifier
     * @return array
     */
    abstract public function rules($identifier): array;
}
