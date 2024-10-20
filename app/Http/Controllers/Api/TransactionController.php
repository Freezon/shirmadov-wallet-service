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

        switch ($type) {
            case TransactionType::CREDIT->value:
                $user->transactions()->create($request->except('target_user_id'));
                $user->update(['balance' => ($user->balance + $amount)]);
                break;
            case TransactionType::DEBIT->value:
                $user->transactions()->create($request->except('target_user_id'));
                $user->update(['balance' => ($user->balance - $amount)]);
                break;
            case TransactionType::TRANSFER->value:
                $user->transactions()->create($request->validated());
                $user->update([
                    'balance' => ($user->balance - $amount),
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
        $balance = auth()->user()->balance;
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
        $transactions = auth()->user()->transactionByUserOrTarget()->get();
        return response()->json([
            'status' => true,
            'data' => TransactionResource::collection($transactions),
        ], 200);
    }

}
