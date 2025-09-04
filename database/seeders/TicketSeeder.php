<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Base batch
        Ticket::factory()->count(30)->create();

        // Ensure variety by status
        Ticket::factory()->state(['status' => 'new'])->count(5)->create();
        Ticket::factory()->state(['status' => 'open'])->count(5)->create();
        Ticket::factory()->state(['status' => 'pending'])->count(5)->create();
        Ticket::factory()->state(['status' => 'closed'])->count(5)->create();
    }

}
