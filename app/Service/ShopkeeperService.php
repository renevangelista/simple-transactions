<?php

namespace App\Service;

use App\Model\Core\Message;
use App\Service\Base\Service;
use App\Model\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;

class ShopkeeperService extends Service
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

        /** @var User $user */
        $user = $message->getData();
        $data['email'] = $user->email;
        return parent::update($data, $id);
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
            'email' => 'required|string|email|max:255|unique:shopkeepers,email',
            'cnpj' => 'required|string|size:14|unique:shopkeepers,cnpj',
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
