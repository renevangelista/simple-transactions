<?php

namespace App\Service;

use App\Model\Core\Message;
use App\Model\Shopkeeper;
use App\Model\User;
use Illuminate\Support\Facades\Validator;

class TransactionService
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->message = new Message();
    }

    /**
     * @param array $data
     * @return Message
     */
    public function transaction(array $data): Message
    {
        $message = $this->validateTransaction($data);
        if ($message->isError()) {
            return $message;
        }

        $message = $this->removeBalancePayer($data);
        if ($message->isError()) {
            return $message;
        }

        if ($data['payee_type'] == 'user') {
            $message = $this->addBalancePayeeUser($data);
            if ($message->isError()) {
                return $message;
            }
        }

        if ($data['payee_type'] == 'shopkeeper') {
            $message = $this->addBalancePayeeShopkeeper($data);
            if ($message->isError()) {
                return $message;
            }
        }

        if ($message->isError()) {
            return $message;
        }

        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function validateTransaction(array $data): Message
    {
        $message = $this->validate($data);
        if ($message->isError()) {
            return $message;
        }

        $message = $this->validateBalance($data);
        if ($message->isError()) {
            return $message;
        }

        $message = $this->validatePayee($data);
        if ($message->isError()) {
            return $message;
        }

        $externalValidatorService = new ExternalValidatorService();
        $message = $externalValidatorService->validate($data);
        if ($message->isError()) {
            return $message;
        }

        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function addBalancePayeeShopkeeper(array $data): Message
    {
        $shopkeeperService = new ShopkeeperService(new Shopkeeper());

        $message = $shopkeeperService->find($data['payee']);
        if ($message->isError()) {
            return $message;
        }

        /** @var Shopkeeper $shopkeeper */
        $shopkeeper = $message->getData();
        $shopkeeper->balance = $shopkeeper->balance + $data['value'];

        $message = $shopkeeperService->update($shopkeeper->toArray(), $data['payee']);

        if ($message->isError()) {
            return $message;
        }

        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function addBalancePayeeUser(array $data): Message
    {
        $userService = new UserService(new User());

        $message = $userService->find($data['payee']);
        if ($message->isError()) {
            return $message;
        }

        /** @var User $user */
        $user = $message->getData();
        $user->balance = $user->balance + $data['value'];

        $message = $userService->update($user->toArray(), $data['payee']);

        if ($message->isError()) {
            return $message;
        }

        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function removeBalancePayer(array $data): Message
    {
        $userService = new UserService(new User());

        $message = $userService->find($data['payer']);
        if ($message->isError()) {
            return $message;
        }

        /** @var User $user */
        $user = $message->getData();
        $user->balance = $user->balance - $data['value'];

        $message = $userService->update($user->toArray(), $data['payer']);

        if ($message->isError()) {
            return $message;
        }

        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function validateBalance(array $data): Message
    {
        $userService = new UserService(new User());
        $message = $userService->find($data['payer']);
        if ($message->isError()) {
            return $message;
        }

        /** @var User $user */
        $user = $message->getData();

        if ($data['value'] > $user->balance) {
            return $this->message->error(
                trans('system.messages.you_do_not_have_enough_balance'),
                null,
                ['value' => trans('system.messages.the_transaction_value_must_be')]
            );
        }
        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function validatePayee(array $data): Message
    {
        if ($data['payer'] == $data['payee']) {
            if ($data['payee_type'] == 'user') {
                return $this->message->error(
                    trans('system.messages.some_field_is_not_valid'),
                    null,
                    ['payee' => trans('system.messages.you_cant_pay_yourself')]
                );
            }
        }
        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param array $data
     * @return Message
     */
    public function validate(array $data): Message
    {
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            return $this->message->error(trans('system.messages.some_field_is_not_valid'), null, $validator->errors());
        }
        return $this->message->success(trans('system.messages.success'), null);
    }

    /**
     * @param int $id
     * @return array
     */
    public function rules(): array
    {
        return [
            'value' => 'required|numeric|gt:0',
            'payer' => 'required|numeric|exists:users,id',
            'payee' => 'required|numeric',
        ];
    }
}
