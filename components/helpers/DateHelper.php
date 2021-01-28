<?php
namespace app\components\helpers;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class DateHelper
 * @package app\components\helpers
 */
class DateHelper
{
    const AGE_MIN = 15;
    const AGE_MAX = 110;

    public static function getYearsArray()
    {
        $prompt = [0 => ''];
        $range  = range(date('Y') - self::AGE_MIN, date('Y') - self::AGE_MAX);
        $range  = array_combine($range, $range);

        return ArrayHelper::merge($prompt, $range);
    }

    public static function getMonthsArray()
    {
        $prompt = [0 => ''];
        $months = array_reduce(range(1, 12), function ($rslt, $m) {
            $rslt[$m] = Yii::$app->formatter->asDate(mktime(0, 0, 0, $m, 10), 'php:F');
            return $rslt;
        });

        return ArrayHelper::merge($prompt, $months);
    }

    public static function getDaysArray()
    {
        $days    = range(0, 31);
        $days[0] = '';

        return $days;
    }
}