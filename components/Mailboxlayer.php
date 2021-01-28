<?php

namespace app\components;

class Mailboxlayer
{
    /**
     * Sends a request.
     *
     * @param string $apikey
     * @param string $email
     * @return bool|object
     */
    public static function check($apikey, $email, $smtpCheck = false)
    {
        $apiUrl = 'https://apilayer.net/api/check';
        $email = urlencode($email);
        $smtpCheck = $smtpCheck ? '' : '&smtp=0';

        $url =  "{$apiUrl}?access_key={$apikey}&email={$email}{$smtpCheck}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return curl_errno($ch) ? false : json_decode($response, true);
    }
}
