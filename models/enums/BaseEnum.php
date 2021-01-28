<?php
namespace app\models\enums;

use yii\helpers\ArrayHelper;

class BaseEnum
{
    public static $titles = [];
    public static $unknown = 'Unknown';

    public static function getTitle($id)
    {
        return ArrayHelper::getValue(static::$titles, $id, static::$unknown);
    }
}
