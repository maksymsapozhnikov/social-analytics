<?php
namespace app\components;

/**
 * swt83/php-numverify
 * @author Scott Travis <scott.w.travis@gmail.com>
 */
class NumVerify
{
    /**
     * Make the request.
     *
     * @param   string  $apikey
     * @param   string  $number
     * @return  object
     */
    public static function run($apikey, $number)
    {
        $endpoint = 'http://apilayer.net/api/validate?access_key=' . $apikey . '&number=' . $number;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $result = false;
        } else {
            $result = json_decode($response);
        }

        curl_close($ch);

        return $result;
    }
}