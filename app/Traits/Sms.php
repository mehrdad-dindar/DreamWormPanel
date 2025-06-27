<?php

namespace App\Traits;

use App\Models\User;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Ippanel\Facades\IPPanel;
use Ippanel\Responses\SendResponse;

trait Sms
{
    const ORDER_SUBMITTED = 'plbga45ztzj0t3l';
    /**
     * ارسال پیامک با پترن
     *
     * @param string $patternCode
     * @param string|array $recipients
     * @param array $patternValues
     * @return SendResponse bulkID یا استثناء
     * @throws GuzzleException
     */
    public function sendPattern(string $patternCode, $recipient, array $patternValues)
    {
        try {
            $bulkID = IPPanel::sendPattern(
                $patternCode,
                "+983000505",
                $recipient,
                $patternValues
            );
            return $bulkID;
        } catch (Exception $e) {
            // مدیریت خطا
            throw $e;
        }
    }

}
