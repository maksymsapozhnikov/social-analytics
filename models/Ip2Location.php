<?php
namespace app\models;

use yii\db\ActiveRecord;

class Ip2Location extends ActiveRecord
{
    public static function tableName()
    {
        return 'ip2location_db';
    }

    public static function getDetails($ip)
    {
        $rows = static::find()
            ->where(['>=', 'ip_to', static::ip2long($ip)])
            ->orderBy(['ip_to' => SORT_ASC])
            ->limit(1)->all();

        if (is_null($rows) || empty($rows)) {
            return null;
        }

        return $rows[0];
    }

    protected static function ip2long($ip)
    {
        $ip = explode(".", $ip);

        return ($ip[3] + $ip[2] * 256 + $ip[1] * 256 * 256 + $ip[0] * 256 * 256 * 256);
    }
}
