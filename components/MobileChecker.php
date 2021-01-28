<?php
namespace app\components;

use app\models\enums\PhoneSystemEnum;
use app\models\PhoneCache;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class MobileChecker extends Component
{
    public static function getDetails($phone)
    {
        if (!self::isValidCountry($phone)) {
            return ['valid' => false];
        }

        $cached = PhoneCache::findOne(['phone' => $phone]);
        if (!is_null($cached)) {
            return [
                'phone' => $cached->phone,
                'valid' => $cached->valid,
                'country' => $cached->country,
                'currency' => $cached->currency,
                'operator' => $cached->operator,
                'payment_system' => PhoneSystemEnum::getTitle($cached->payment_system),
            ];
        }

        $check = self::getTransferToData($phone);
        /** @var TransferTo $tt */
        $tt = \Yii::$app->transferTo;
        if (!$tt->isError() || $tt->errorCode == '101') {
            $isValid = !$tt->isError();
            $cached = new PhoneCache([
                'phone' => $check['destination_msisdn'],
                'valid' => $isValid,
                'country' => $isValid ? $check['country'] : null,
                'country_id' => $isValid ? $check['countryid'] : null,
                'operator' => $isValid ? $check['operator'] : null,
                'operator_id' => $isValid ? $check['operatorid'] : null,
                'currency' => $isValid ? $check['destination_currency'] : null,
            ]);

            $cached->save();

            return [
                'phone' => $cached->phone,
                'valid' => $cached->valid,
                'country' => $cached->country,
                'currency' => $cached->currency,
                'operator' => $cached->operator,
                'payment_system' => PhoneSystemEnum::getTitle($cached->payment_system),
            ];
        }

        return ['valid' => null];
    }

    protected static function isValidCountry($phone)
    {
        $query = 'select count(*) from phone_country where :phone like concat(code, \'%\') limit 1';

        return !!\Yii::$app->db->createCommand($query)->bindValue('phone', $phone)->queryScalar();
    }

    protected static function getNumVerifyData($phone)
    {
        $apiKey = \Yii::$app->params['numverify']['key'];

        return NumVerify::run($apiKey, $phone);
    }

    public static function getTransferToData($phone)
    {
        /** @var TransferTo $tt */
        $tt = \Yii::$app->transferTo;

        $mocks = [
            [
                'areacode' => '61',
                'name' => 'Australia',
                'currency' => 'AUD',
                'operator' => 'Unknown',
                'payment_system' => PhoneSystemEnum::UNDEFINED,
            ],
            [
                'areacode' => '420',
                'name' => 'Czech Republic',
                'currency' => 'CZK',
                'operator' => 'Unknown',
                'payment_system' => PhoneSystemEnum::UNDEFINED,
            ],
            [
                'areacode' => '421',
                'name' => 'Slovakia',
                'currency' => 'SKK',
                'operator' => 'Unknown',
                'payment_system' => PhoneSystemEnum::UNDEFINED,
            ],
        ];

        foreach($mocks as $mock) {
            $mockResult = self::checkNumVerifyMock($phone, $mock);
            if (!is_null($mockResult)) {
                return $mockResult;
            }
        }

        return $tt->checkPhone($phone);
    }

    protected static function checkNumVerifyMock($phone, $countryDetails)
    {
        $tt = \Yii::$app->transferTo;

        $phoneContainsAreaCode = substr($phone, 0, strlen($countryDetails['areacode'])) === $countryDetails['areacode'];

        if (!$phoneContainsAreaCode) {
            return null;
        }

        $tt->errorCode = null;
        $tt->errorMessage = null;

        $result = self::getNumVerifyData($phone);

        if (ArrayHelper::getValue($result, 'valid')) {
            $data = [
                'valid' => true,
                'destination_msisdn' => $phone,
                'country' => $countryDetails['name'],
                'countryid' => null,
                'operator' => ArrayHelper::getValue($result, 'carrier'),
                'operatorid' => null,
                'destination_currency' => $countryDetails['currency'],
                'product_list' => [],
            ];
        } else {
            $tt->errorCode = '101';
            $tt->errorMessage = $countryDetails['country'] . ' is out of range.';

            $data = [
                'valid' => false,
            ];
        }

        return $data;
    }
}
