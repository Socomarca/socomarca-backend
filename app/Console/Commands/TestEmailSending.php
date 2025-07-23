<?php

namespace App\Console\Commands;

use App\Jobs\SendRawTestEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailSending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email-sending {email-address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $address = $this->argument('email-address');
        SendRawTestEmail::dispatch($address, 'Test email from ' . config('app.name'), 'Test email from ' . config('app.name'));
    }
}
