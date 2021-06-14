<?php

namespace App\Service;

use App\Model\Core\Message;
use App\Service\Base\Service;
use Illuminate\Support\Arr;

class ShopkeeperService extends Service
{
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

        $dataModel = Arr::only($data, $this->dataManager->getModel()->getFillable());
        $model = $this->dataManager->update($dataModel, $identifier);

        if ($model) {
            return $this->message->success(trans('system.messages.updated_successfully'), $model);
        }

        return $this->message->error(trans('system.messages.it_was_not_possible_update'), null, '');
    }

    /**
     * @param int $identifier
     * @return array
     * @SuppressWarnings("unused")
     */
    public function rules($identifier): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:shopkeepers,email',
            'cnpj' => 'required|string|size:14|unique:shopkeepers,cnpj',
            'password' => 'required|string|min:6',
        ];
    }
}
