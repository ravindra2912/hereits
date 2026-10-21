<?php

namespace App\Console\Commands;

use App\Models\Quotation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class QuotationExpiryCheckTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:quotation-check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-expire quotations where valid_until date and time has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for expired quotations...");
        Log::info("Auto-expire quotations task started at " . now());

        $count = Quotation::whereIn('status', [Quotation::STATUS_PENDING, Quotation::STATUS_INPROGRESS])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now())
            ->update(['status' => Quotation::STATUS_EXPIRED]);

        $this->info("Successfully expired {$count} quotation(s).");
        Log::info("Auto-expire quotations task finished at " . now() . ". Total expired: {$count}");

        return Command::SUCCESS;
    }
}
