<?php
namespace app\models;

use app\components\AppHelper;
use yii\db\ActiveRecord;

class IpBlacklist extends ActiveRecord
{
    public static function tableName()
    {
        return 'ip_blacklist';
    }

    public function rules()
    {
        return [
            [['ip_v4'], 'ip', 'ipv6' => false, 'message' => 'Invalid IP address.'],
            [['ip_v4'], 'unique', 'message' => 'This IP already blocked.'],
            [['ip_v4'], 'safe'],
        ];
    }

    public static function findByIp($ip)
    {
        return self::findOne(['ip_dec' => self::ip2long($ip)]);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->since_dt = AppHelper::timeUtc();
        }

        $this->ip_dec = $this->ip2long($this->ip_v4);

        return parent::save($runValidation, $attributeNames);
    }

    protected static function ip2long($ip)
    {
        $ip = explode(".", $ip);

        return ($ip[3] + $ip[2] * 256 + $ip[1] * 256 * 256 + $ip[0] * 256 * 256 * 256);
    }

    public function getDetails()
    {
        static $details = [];

        $ip = $this->ip_v4;

        if (!isset($details[$ip])) {
            $details[$ip] = Ip2Location::getDetails($ip);
        }

        return $details[$ip];
    }
}
