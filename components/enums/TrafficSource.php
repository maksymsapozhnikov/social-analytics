<?php
namespace app\components\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class TrafficSource
 * @package app\components\enums
 */
class TrafficSource extends BaseEnum
{
    const TAPJOY = 'tpj';
    const FYBER = 'fyb';
    const LUCID = 'luc';
    const TGM = 'tgm';
    const POLLFISH = 'poll';
    const CINT = 'cint';

    protected static $list = [
        self::TAPJOY => 'Tapjoy',
        self::FYBER => 'Fyber',
        self::LUCID => 'Lucid',
        self::TGM => 'TGM',
        self::POLLFISH => 'Pollfish',
        self::CINT => 'Cint',
    ];
}