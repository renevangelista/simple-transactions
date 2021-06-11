<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\User;
use App\Service\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new TransactionService();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function transaction(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::guard()->user();

        $request['payer'] = $user->id;

        DB::beginTransaction();
        $message = $this->service->transaction($request->all());

        if ($message->isError()) {
            DB::rollBack();
            return response([
                "message" => $message->getMessage(),
                "errors" => $message->getErrors(),
            ], 400);
        }

        DB::commit();
        return response([
            "message" => $message->getMessage(),
        ]);
    }
}
