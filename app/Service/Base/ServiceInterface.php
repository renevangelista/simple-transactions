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
     * @param $identifier
     * @param array $columns
     * @return Message
     */
    public function find($identifier, array $columns = ['*']): Message;

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
     * @param array $data
     * @return Message
     */
    public function create(array $data): Message;

    /**
     * @param array $data
     * @param $identifier
     * @return Message
     */
    public function update(array $data, $identifier): Message;

    /**
     * @return Guard
     */
    public function guard(): Guard;

    /**
     * @param array $data
     * @param null $identifier
     * @return mixed
     */
    public function validate(array $data, $identifier = null);

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
     * @param $identifier
     * @return array
     */
    public function rules($identifier): array;
}
