<?php

namespace App\Http\Resources;

use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();

        // Сообщения должны формироваться через trans() / trans_choice()
        $who = $user->id === $this->user->id ? 'Вы перевели пользователю' : 'Вам перевели пользователь';

        $comment = match ($this->type) {
            TransactionType::CREDIT->value => 'Вы зачисли ' . $this->amount . ' в ' . $this->created_at->format('d.m.Y H:i'),
            TransactionType::DEBIT->value => 'Вы списали ' . $this->amount . ' в ' . $this->created_at->format('d.m.Y H:i'),
            TransactionType::TRANSFER->value => $who . ' ' . $this->user->name . ' ' . $this->amount . ' РУБ в ' . $this->created_at->format('d.m.Y H:i')
        };

        return [
            'comment' => $comment,
            'date' => $this->created_at,
        ];
    }
}
