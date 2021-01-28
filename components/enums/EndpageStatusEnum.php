<?php
namespace app\components\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class EndpageStatusEnum
 * @package app\components\enums
 */
class EndpageStatusEnum extends BaseEnum
{
    const FIN = 'fin';
    const DSQ = 'dsq';
    const SCR = 'scr';
    const QFL = 'qfl';

    protected static $list = [
        self::FIN => 'Done',
        self::DSQ => 'Disqualified',
        self::SCR => 'Screened out',
        self::QFL => 'Quota full',
    ];
}