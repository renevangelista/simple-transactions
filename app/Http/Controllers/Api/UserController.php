<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\User;
use App\Service\UserService;

class UserController extends Controller
{
    protected $service;
    protected $transactionService;

    public function __construct(User $user)
    {
        $this->service = new UserService($user);
    }
}
