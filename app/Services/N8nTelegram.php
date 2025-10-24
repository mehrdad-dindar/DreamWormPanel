<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class N8nTelegram
{
    protected $username;
    protected $password;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->username = 'mehrdad';
        $this->password = 'MehrDad!133665';
    }

    /**
     * Send a POST request to the Telegram webhook with Basic Authentication.
     *
     * @param array $data The data to be sent in the request body
     * @param string $username Basic Auth username
     * @param string $password Basic Auth password
     * @return array
     * @throws ConnectionException
     */
    public function sendToN8nWebhook(array $data): array
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->post('https://dreamworm.app.n8n.cloud/webhook/to-telegram', $data);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'data' => $response->json(),
            ];
        }

        return [
            'status' => 'error',
            'message' => $response->reason(),
            'status_code' => $response->status(),
        ];
    }
}
