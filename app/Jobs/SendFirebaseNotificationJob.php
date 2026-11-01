<?php

namespace App\Jobs;

use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    protected array $fcmTokens;
    protected string $title;
    protected string $message;
    protected ?string $redirectUrl;
    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $fcmTokens, string $title, string $message, ?string $redirectUrl = null, array $data = [])
    {
        $this->fcmTokens = array_values(array_filter(array_unique($fcmTokens)));
        $this->title = $title;
        $this->message = $message;
        $this->redirectUrl = $redirectUrl;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->fcmTokens as $token) {
            FirebaseNotificationService::sendPush(
                fcmToken: $token,
                title: $this->title,
                message: $this->message,
                redirectUrl: $this->redirectUrl,
                data: $this->data
            );
        }
    }
}
