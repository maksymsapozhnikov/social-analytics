<?php
namespace app\components;

use function Sodium\add;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class GoogleApi extends Component
{
    public static function getAddress($lon, $lat)
    {
        $apiKey = \Yii::$app->params['google']['apiKey'];
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?language=en&location_type=ROOFTOP'
            . '&latlng=' . trim($lat) . ',' . trim($lon)
            . '&key=' . $apiKey;

        try {
            $response = file_get_contents($url);
            $reversed = Json::decode($response);
            $address = ArrayHelper::getValue($reversed, 'results.0.formatted_address');
        } catch (\Exception $e) {
            return false;
        }

        return $address;
    }
}
