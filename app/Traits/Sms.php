<?php

namespace App\Traits;

use App\Models\User;
use Ippanel\Client;

trait Sms
{
    public function sendPattern(Client $ippanel, $pattern,User $user, $params)
    {
        $response = $ippanel->sendPattern(
            $pattern,  // Your pattern code
            '+981000xxxx',   // Sender number
            '+98'.intval($user->phone), // Recipient
            $params // Pattern parameters
        );

        if ($response->isSuccessful()) {
            // Pattern message sent successfully
            $data = $response->getData();
            // Process data...
        } else {
            // Handle error
        }
    }

}
