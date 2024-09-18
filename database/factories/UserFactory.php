<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
  protected $model = User::class;

        public function definition()
        {
            return [
                'name' => $this->faker->name,
                'profile' => 'default.png',
                'adresse' => $this->faker->address,
                'email' => $this->faker->unique()->safeEmail,
                'telephone' => $this->faker->phoneNumber,
                'password' => Hash::make('password'),
                'role' => $this->faker->randomElement(['client', 'producteur']),
            ];
        }
    

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
