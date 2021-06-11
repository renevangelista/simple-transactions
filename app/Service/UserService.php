<?php

namespace App\Service;

use App\Model\Core\Message;
use App\Service\Base\Service;
use App\Model\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;

class UserService extends Service
{
    /**
     * @param array|string[] $columns
     * @return Message
     */
    public function all(array $columns = ['*']): Message
    {
        return $this->findBy(
            [],
            ['name' => 'asc'],
            null,
            null,
            $columns
        );
    }

    /**
     * @return mixed
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * @param int $id
     * @return array
     */
    public function rules($id): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'cpf' => 'required|string|size:11|unique:users,cpf',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}
