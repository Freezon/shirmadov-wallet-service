<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    // Не указан тип (array)
    protected $fillable = [
        'user_id',
        'amount',
        // Данное поле не имеет надобности, т.к. amount не unsigned
        // $this->amount > 0 ? TransactionType::credit : TransactionType::debit
        'type',
        'target_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
