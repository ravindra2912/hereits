<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppointmentBooking;
use Illuminate\Support\Facades\Log;

class AppoinmentBookingCheckTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:appoinment-auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto cancel appoinment past booking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Auto cancel appoinment start at " . date('y-m-d-h-i-s'));
        
        AppointmentBooking::whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->where('booking_date', '<', now())
            ->update(['status' => 'auto_cancelled']);

        Log::info("Auto cancel appoinment end at " . date('y-m-d-h-i-s'));
    }
}
