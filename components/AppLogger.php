<?php
namespace app\components;

class AppLogger
{
    public static function error($message)
    {
        \Yii::error($message);
    }

    public static function warning($message)
    {
        \Yii::warning($message);
    }
}
