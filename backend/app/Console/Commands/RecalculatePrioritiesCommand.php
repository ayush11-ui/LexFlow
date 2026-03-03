<?php

namespace App\Console\Commands;

use App\Services\PriorityService;
use Illuminate\Console\Command;

class RecalculatePrioritiesCommand extends Command
{
    protected $signature = 'lexflow:priorities-recalculate';
    protected $description = 'Recalculate all active case priority scores.';

    public function __construct(private readonly PriorityService $priorityService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->priorityService->recalculateAll();
        $this->info("Recalculated priorities for {$count} active cases.");

        return self::SUCCESS;
    }
}
