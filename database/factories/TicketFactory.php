<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string)Str::ulid(),
            'subject' => $this->faker->sentence(6, true),
            'body' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement(Ticket::STATUSES),
            'category' => $this->faker->randomElement(Ticket::CATEGORIES),
            'note' => $this->faker->boolean(40) ? $this->faker->sentence(10) : null,
            'explanation' => $this->faker->boolean(70) ? $this->faker->sentence(12) : null,
            'confidence' => $this->faker->boolean(70) ? $this->faker->randomFloat(2, 0.50, 0.99) : null,
        ];
    }
}
