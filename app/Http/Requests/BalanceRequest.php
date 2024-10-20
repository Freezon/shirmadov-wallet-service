<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Rules\SufficientBalance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class BalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(TransactionType::class)],
            'amount' => ['required', 'decimal:2', 'min:0', new SufficientBalance($this->input('type'))],
            'target_user_id' => [
                Rule::requiredIf(fn() => $this->input('type') === TransactionType::TRANSFER->value),
                Rule::when(
                    fn() => $this->input('type') === TransactionType::TRANSFER->value,
                    ['exists:users,id', Rule::notIn([auth()->id()]), 'exists:users,id']
                ),
            ],
        ];
    }
}
