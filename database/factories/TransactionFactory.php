<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'=>User::factory(),
            'amount'=>$this->faker->randomFloat(2,0,1000),
            'type'=>$this->faker->randomElement(TransactionType::ALL),
            'target_user_id'=>User::factory(),
            'comment'=>$this->faker->sentence(),
            'created_at'=>now(),
            'updated_at'=>now(),
        ];
    }
}
