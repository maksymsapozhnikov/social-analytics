<?php
namespace app\components\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class QuestionType
 * @package app\components\enums
 */
class QuestionType extends BaseEnum
{
    const CONSENT = 'consent';
    const DATE = 'date';
    const EMAIL = 'email';
    const OPTIONS = 'options';
    const TEXT = 'text';

    protected static $list = [
        self::CONSENT => 'Consent',
        self::DATE => 'Date',
        self::EMAIL => 'Email',
        self::OPTIONS => 'Radio list',
        self::TEXT => 'Text input',
    ];
}