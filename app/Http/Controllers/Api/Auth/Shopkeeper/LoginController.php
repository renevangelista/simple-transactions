<?php

namespace App\Http\Controllers\Api\Auth\Shopkeeper;

use App\Http\Controllers\Controller;
use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $response = ["message" => 'User does not exist'];
            return response($response, 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            $response = ["message" => "Password mismatch"];
            return response($response, 422);
        }

        $token = $user->createToken('Simple Transactions Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response);
    }
}
