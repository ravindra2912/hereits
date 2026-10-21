<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait GoogleSheet
{
    public function sendGoogleSheetWebhook($fields, $webhook)
    {

        // Flatten the structured fields list to a flat key-value payload
        $payload = [];
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (is_array($field) && isset($field['label'])) {
                    $payload[$field['label']] = $field['value'] ?? '';
                } else if (!is_array($field)) {
                    // Fallback if fields is already flat
                    $payload = $fields;
                    break;
                }
            }
        } else {
            $payload = $fields;
        }

        $response = Http::post($webhook, $payload);

        return [
            'status' => $response->status(),
            'body'   => $response->body(),
            'json'   => $response->json(),
        ];
    }

    public function store($fields, $webhook)
    {
        return $this->sendGoogleSheetWebhook($fields, $webhook);
    }

    /**
     * An alternative helper method to avoid naming collisions with 
     * controller-native store() methods.
     * 
     * @param array $fields
     * @param string $webhook
     * @return array|null
     */
    public function storeToGoogleSheet($fields, $webhook)
    {
        return $this->sendGoogleSheetWebhook($fields, $webhook);
    }
}
