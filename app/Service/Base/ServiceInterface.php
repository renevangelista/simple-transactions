<?php

namespace App\Service\Base;

use App\Model\Core\Message;
use Illuminate\Contracts\Auth\Guard;

/**
 * Interface ServiceInterface
 * @package App\Service\Base
 */
interface ServiceInterface
{
    /**
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*']);

    /**
     * @param $id
     * @param array $columns
     * @return Message
     */
    public function find($id, array $columns = ['*']): Message;

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
    ): Message;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param array $columns
     * @return Message
     */
    public function findOneBy(
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null,
        array $columns = ['*']
    ): Message;

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
    ): Message;

    /**
     * @param array $data
     * @return Message
     */
    public function create(array $data): Message;

    /**
     * @param array $data
     * @param $id
     * @return Message
     */
    public function update(array $data, $id): Message;

    /**
     * @param $id
     * @return Message
     */
    public function delete($id): Message;

    /**
     * @return Guard
     */
    public function guard(): Guard;

    /**
     * @param array $data
     * @param null $id
     * @return mixed
     */
    public function validate(array $data, $id = null);

    /**
     * @param array $relations
     * return Service
     */
    public function with(array $relations);

    /**
     * return void
     */
    public function clearWith();

    /**
     * @param $id
     * @return array
     */
    public function rules($id): array;

    /** @return array */
    public function messages(): array;
}
