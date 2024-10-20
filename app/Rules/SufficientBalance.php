<?php

namespace App\Rules;

use App\Enums\TransactionType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientBalance implements ValidationRule
{
    protected string $type;
    public function __construct(string $type)
    {
        $this->type = $type;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (in_array($this->type, [TransactionType::DEBIT->value, TransactionType::TRANSFER->value])) {
            $user = auth()->user();

            if ($user->balance < $value) {
                $fail('Your balance is insufficient');
            }
        }
    }


}
