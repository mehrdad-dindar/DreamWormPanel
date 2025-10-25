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
    const FOLLOW_UP = 'hsbmg1dsak5og6l';
    /**
     * ارسال پیامک با پترن
     *
     * @param string $patternCode
     * @param string|array $recipients
     * @param array $patternValues
     * @return SendResponse bulkID یا استثناء
     * @throws GuzzleException
     */
    public function sendPattern(string $patternCode, $phone, array $patternValues)
    {
        try {
            $bulkID = IPPanel::sendPattern(
                $patternCode,
                "+983000505",
                '+98'. (int)$phone,
                $patternValues
            );
            return $bulkID;
        } catch (Exception $e) {
            // مدیریت خطا
            throw $e;
        }
    }

}
