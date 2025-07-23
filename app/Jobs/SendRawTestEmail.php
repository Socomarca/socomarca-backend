<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendRawTestEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $to,
        private string $subject,
        private string $body)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $address = $this->to;
        $subject = $this->subject;
        $body = $this->body;
        Mail::raw($body, function($message) use ($address, $subject, $body) {
            $message->to($address)
                ->subject($subject);
        });
    }
}
