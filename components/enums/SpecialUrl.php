<?php
namespace app\components\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class SpecialUrl
 * @package app\components\enums
 */
class SpecialUrl extends BaseEnum
{
    const SURVEY_FINISHED = '/error/finished';
    const DISQUALIFIED = '/error/dsq';

    const STATUS_DSQ = '/s/ds';
    const STATUS_SCR = '/s/sc';
    const STATUS_FIN = '/s/fn';

    protected static $list = [
        self::SURVEY_FINISHED => 'Survey finished',
        self::DISQUALIFIED => 'Respondent disqualified',

        self::STATUS_DSQ => 'Survey Status Disqualified',
        self::STATUS_SCR => 'Survey Status Screened Out',
        self::STATUS_FIN => 'Survey Status Finished',
    ];
}