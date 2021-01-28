<?php
namespace app\models\enums;

class PhoneSystemEnum extends BaseEnum
{
    const UNDEFINED = 0;
    const PREPAID = 1;
    const POSTPAID = 2;

    public static $titles = [
        self::UNDEFINED => 'Unknown',
        self::PREPAID => 'Prepaid',
        self::POSTPAID => 'Postpaid',
    ];
}
