<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PuhsNotificationToAllUser implements ShouldQueue
{
    use Queueable;

    protected array $params;

    /**
     * Create a new job instance.
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $body = [
            'app_id' => env('ONESIGNAL_APP_ID'),
            // 'include_player_ids' => $this->params['include_player_ids'],
            'included_segments' => ['All'], // This sends to all subscribed users
            'headings' => ['en' => $this->params['title']],
            'contents' => ['en' => $this->params['message']],
            'url' => isset($this->params['url']) ? $this->params['url'] : '',
            // ✅ Add Image Here
            // 'chrome_web_image' => 'https://yourdomain.com/images/offer-banner.jpg', // For Web
            // 'big_picture' => 'https://yourdomain.com/images/offer-banner.jpg',      // For Android
            // // ✅ iOS image attachment
            // 'ios_attachments' => [
            //     'id1' => 'https://yourdomain.com/images/ios-offer.jpg',
            // ],

            // ✅ Custom data
            // 'data' => isset($this->params['data']) != [] ? $this->params['data'] : [],

            // ✅ Action Buttons
            // 'buttons' => [
            //     [
            //         'id' => 'yes-button',          // Required: unique button ID
            //         'text' => 'Yes',               // Button text
            //         // 'icon' => 'https://cdn-icons-png.flaticon.com/512/1828/1828817.png', // Optional
            //     ],
            //     [
            //         'id' => 'no-button',
            //         'text' => 'No',
            //         // 'icon' => 'https://cdn-icons-png.flaticon.com/512/1828/1828843.png',
            //     ],
            // ],


        ];

        if (isset($this->params['data']) && $this->params['data'] != [] && is_array($this->params['data'])) {
            $body['data'] = $this->params['data'];
        }

        // Schedule the notification
        if (isset($this->params['schedule']) && !empty($this->params['schedule']) && Carbon::parse($this->params['schedule'])->isFuture()) {
            // 'send_after' => now()->addMinutes(1)->toRfc7231String(), // Schedule 10 min from now
            $body['send_after'] = Carbon::parse($this->params['schedule'])->toRfc7231String();
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
        ])->post('https://onesignal.com/api/v1/notifications', $body);

        // dd($response->json());
    }
}
