<?php


declare(strict_types=1);
namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Base batch with a good mix
        Ticket::factory()->count(30)->create();

        // Ensure each status is represented
        Ticket::factory()->asNew()->count(5)->create();
        Ticket::factory()->asOpen()->count(5)->create();
        Ticket::factory()->asPending()->count(5)->create();
        Ticket::factory()->asClosed()->count(5)->create();

        // A few edge cases for UI/testing:
        // - Very long body
        Ticket::factory()->create([
            'subject' => 'Bulk import fails when CSV is large',
            'body'    => str_repeat('This is a long body paragraph. ', 80),
            'status'  => 'open',
            'category'=> 'Technical',
            'note'    => 'User provided sample file; reproduces locally.',
        ]);


        // - No category, but with note
        Ticket::factory()->create([
            'subject'  => 'Question about enterprise pricing',
            'category' => null,
            'status'   => 'pending',
            'note'     => 'Escalated to sales.',
        ]);

        // - Explanation/confidence present (pretend previously classified)
        Ticket::factory()->create([
            'subject'     => 'Refund request for accidental charge',
            'status'      => 'closed',
            'category'    => 'Billing',
            'explanation' => 'Mentions refund and billing keywords. Bought a new donut',
            'confidence'  => 0.91,
        ]);
    }

}
