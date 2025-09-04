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
        // More realistic subjects + bodies
        $subjects = [
            'Unable to login to account',
            'Billing discrepancy on last invoice',
            'Password reset not working',
            'App crashes on submit',
            'Charge appeared twice on card',
            'Might have eaten a donut that was not mine',
            'Need help updating profile email',
            '2FA code not received',
            'Cannot upload attachments',
            'Subscription cancellation issue',
            'Unexpected error on checkout',
            'Somebody ate my donut >:('
        ];

        $status     = $this->faker->randomElement(Ticket::STATUSES);
        $category   = $this->faker->randomElement([null, ...Ticket::CATEGORIES]); // some nulls
        $hasNote    = $this->faker->boolean(40);
        $hasExplain = $this->faker->boolean(70);


        return [
            'subject'     => $this->faker->randomElement($subjects),
            'body'        => $this->faker->paragraphs($this->faker->numberBetween(2, 5), true),
            'status'      => $status,
            'category'    => $category,
            'note'        => $hasNote ? $this->faker->sentence(10) : null,
            'explanation' => $hasExplain ? $this->faker->sentence(12) : null,
            'confidence'  => $hasExplain ? $this->faker->randomFloat(2, 0.50, 0.99) : null,
        ];
    }

    // Optional helpers if you like named states (safe names)
    public function asNew(): static     { return $this->state(fn() => ['status' => 'new']); }
    public function asOpen(): static    { return $this->state(fn() => ['status' => 'open']); }
    public function asPending(): static { return $this->state(fn() => ['status' => 'pending']); }
    public function asClosed(): static  { return $this->state(fn() => ['status' => 'closed']); }
}
