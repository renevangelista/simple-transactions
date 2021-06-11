<?php

namespace App\Http\Controllers\Api\Auth\Shopkeeper;

use App\Http\Controllers\Controller;
use App\Model\Shopkeeper;
use App\Model\User;
use App\Service\Base\Service;
use App\Service\ShopkeeperService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $shopkeeperService = new ShopkeeperService(new Shopkeeper());

        $message = $shopkeeperService->validate($request->all());
        if ($message->isError()) {
            return response([
                "message" => $message->getMessage(),
                "errors" => $message->getErrors(),
            ], 422);
        }

        $request['password'] = Hash::make($request['password']);

        $message = $shopkeeperService->create($request->all());
        if ($message->isError()) {
            return response([
                "message" => $message->getMessage(),
                "errors" => $message->getErrors(),
            ], 400);
        }

        /** @var User $user */
        $user = $message->getData();

        $token = $user->createToken('Simple Transactions Password Grant Client')->accessToken;

        return response(['token' => $token]);
    }
}
