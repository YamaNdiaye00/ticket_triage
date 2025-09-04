<?php

namespace App\Console\Commands;

use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Console\Command;

class TicketsBulkClassify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    /**
     * Run like:
     *  php artisan tickets:bulk-classify
     *  php artisan tickets:bulk-classify --limit=200 --only-missing
     *  php artisan tickets:bulk-classify --force             (reclassify all)
     */
    protected $signature = 'tickets:bulk-classify
                            {--limit= : Max tickets to enqueue}
                            {--only-missing : Only tickets missing explanation or confidence}
                            {--force : Reclassify all, ignoring only-missing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue classification jobs for many tickets at once';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $onlyMissing = (bool) $this->option('only-missing');
        $force       = (bool) $this->option('force');
        $limitOpt    = $this->option('limit');
        $limit       = is_numeric($limitOpt) ? (int) $limitOpt : null;

        if ($force) {
            $onlyMissing = false;
        }

        $query = Ticket::query();

        if ($onlyMissing) {
            $query->where(function ($q) {
                $q->whereNull('explanation')
                    ->orWhereNull('confidence');
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $count = 0;
        $this->info('Queuing classification jobs...');

        $query->orderBy('created_at', 'desc')
            ->chunk(200, function ($tickets) use (&$count) {
                /** @var Ticket $t */
                foreach ($tickets as $t) {
                    ClassifyTicket::dispatch($t);
                    $count++;
                }
            });

        $this->info("Queued {$count} ticket(s) for classification.");

        if (config('queue.default', env('QUEUE_CONNECTION', 'sync')) !== 'sync') {
            $this->line('Reminder: queue is async. Run: php artisan queue:work');
        }

        return self::SUCCESS;
    }
}
