<?php
namespace app\components\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class S2sSettings
 * @package app\components\enums
 */
class SurveySettings extends BaseEnum
{
    const FYBER_DSQ = 'pb-fyb-dsq';
    const FYBER_SCR = 'pb-fyb-scr';
    const FYBER_FIN = 'pb-fyb-fin';
    const TAPJOY_DSQ = 'pb-tpj-dsq';
    const TAPJOY_SCR = 'pb-tpj-scr';
    const TAPJOY_FIN = 'pb-tpj-fin';

    protected static $list = [
        self::FYBER_DSQ => 'Call Fyber postback on DSQ',
        self::FYBER_SCR => 'Call Fyber postback on SCR',
        self::FYBER_FIN => 'Call Fyber postback on FIN',
        self::TAPJOY_DSQ => 'Call Tapjoy postback on DSQ',
        self::TAPJOY_SCR => 'Call Tapjoy postback on SCR',
        self::TAPJOY_FIN => 'Call Tapjoy postback on FIN',
    ];
}