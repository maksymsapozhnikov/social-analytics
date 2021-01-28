<?php
namespace app\components;

use app\models\Ip2Location;
use yii\base\Component;
use yii\validators\IpValidator;

class IpChecker extends Component
{
    public static function getDetails($ip)
    {
        $validator = new IpValidator(['ipv6' => false]);

        try {
            if ($validator->validate($ip)) {
                $data = Ip2Location::getDetails($ip);
                if (!is_null($data)) {
                    return [
                        'code' => $data->country_code,
                        'country' => $data->country_name,
                        'region' => $data->region_name,
                        'city' => $data->city_name,
                    ];
                }
            }
        } catch (\Exception $e) {
        }

        return null;
    }
}
