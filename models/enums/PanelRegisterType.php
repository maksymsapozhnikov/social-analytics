<?php
namespace app\models\enums;

/**
 * Class PanelRegisterType
 * @package app\models\enums
 */
class PanelRegisterType extends \yii2mod\enum\helpers\BaseEnum
{
    const NONE = 0;
    const ASK_EMAIL = 1;

    protected static $list = [
        self::NONE => 'Do not register to the TGM Portal',
        self::ASK_EMAIL => 'Register asking respondent email',
    ];
}