<?php

namespace App\Http\Controllers\Api\Auth\User;

use App\Http\Controllers\Controller;
use App\Model\User;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $userService = new UserService(new User());

        $message = $userService->validate($request->all());
        if ($message->isError()) {
            return response([
                "message" => $message->getMessage(),
                "errors" => $message->getErrors(),
            ], 422);
        }

        $request['password'] = Hash::make($request['password']);

        $message = $userService->create($request->all());
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
