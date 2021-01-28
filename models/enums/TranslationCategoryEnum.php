<?php
namespace app\models\enums;

use yii2mod\enum\helpers\BaseEnum as YiiEnum;

/**
 * Class TranslationCategoryEnum
 * @package app\models\enums
 */
class TranslationCategoryEnum extends YiiEnum
{
    const RECRUITMENT = 'recruitment';
    const SURVEY_PROCESS = 'survey-process';

    protected static $list = [
        self::RECRUITMENT => 'Recruitment survey',
        self::SURVEY_PROCESS => 'Survey process',
    ];
}