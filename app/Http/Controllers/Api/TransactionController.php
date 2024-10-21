<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceRequest;
use App\Http\Resources\TransactionResource;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
class TransactionController extends Controller
{
    /**
     * Create and Update balance
     *
     * @param BalanceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAndUpdateBalance(BalanceRequest $request)
    {
        $user = auth()->user();
        $type = $request->validated('type');
        $amount = $request->validated('amount');
        $targetUserId = $request->validated('target_user_id');

		// Т.к. происходит несколько действий, которые должны происхоить только вместе,
		// нужно использовать \DB::transaction(...)

        switch ($type) {
            case TransactionType::CREDIT->value:
				// Не безопасно! Нужно $request->safe()->except('target_user_id')
                $user->transactions()->create($request->except('target_user_id'));
				// Не безопасно из-за неатамарности! Нужно: $user->update(['balance' => \DB::raw("`users`.`balance` + $amount")]);
                $user->update(['balance' => ($user->balance + $amount)]);
                break;
            case TransactionType::DEBIT->value:
				// Не безопасно! Нужно $request->safe()->except('target_user_id')
                $user->transactions()->create($request->except('target_user_id'));
				// Не безопасно из-за неатамарности! Нужно: $user->update(['balance' => \DB::raw("`users`.`balance` - $amount")]);
                $user->update(['balance' => ($user->balance - $amount)]);
                break;
            case TransactionType::TRANSFER->value:
                // Записей в таблицу транзакций должно быть 2
                // первая - списание с пользователя 1
                // вторая - зачисление на пользователя 2
                $user->transactions()->create($request->validated());
				// Не безопасно из-за неатамарности! Нужно: $user->update(['balance' => \DB::raw("`users`.`balance` - $amount")]);
                $user->update([
                    'balance' => ($user->balance - $amount),
					# в пользователе нет такого поля, при определенных настройках это приведет к 500
                    'target_user_id' => $targetUserId
                ]);
                User::where('id', $targetUserId)
                    ->update([
                        'balance' => \DB::raw('COALESCE(balance, 0) + ' . $amount)
                    ]);
                break;
            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Not completed transaction',
                ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Operation completed successfully',
        ], 200);
    }

    /**
     * Show user balance
     *
     * @param Request $request
     * @param CurrencyService $currencyService
     * @return \Illuminate\Http\JsonResponse
     */
    public function showBalance(Request $request, CurrencyService $currencyService)
    {
        // Нет валидации запроса!
        $balance = auth()->user()->balance;
        // Для чего запрашивается перевод, если он может и не понадобиться?
        $curencyData = $currencyService->convert('RUB');
        $curency = $request->input('currency');

        if ($curency) {
            if (isset($curencyData['data'][$curency])) {
                $balance = number_format($balance * $curencyData['data'][$curency], 2) . ' ' . $curency;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Currency not found',
                ], 400);
            }
        } else {
            $balance = $balance .' RUB';
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => [
                'amount' => $balance
            ],
        ], 200);
    }

    /**
     * List history balance
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showTransactions()
    {
        /***
         * Правильнее будет так:
         * $transactions = Transaction::whereUser(auth()->user());
         *
         * при этом в Transaction нужно добавить скоуп scopeWhereUser($query, User $id): void
         */
        $transactions = auth()->user()->transactionByUserOrTarget()->get();
        return response()->json([
            'status' => true,
            'data' => TransactionResource::collection($transactions),
        ], 200);
    }

}
