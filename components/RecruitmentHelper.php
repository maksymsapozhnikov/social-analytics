<?php
namespace app\components;

use app\components\enums\QuestionType;
use yii\helpers\ArrayHelper;

/**
 * Class RecruitmentHelper
 * @package app\components
 */
class RecruitmentHelper
{
    /**
     * Returns
     * @param string $elType
     * @return string
     */
    public static function getElementView($elType)
    {
        $defaultInput = '_text';
        $map = [
            QuestionType::CONSENT => '_consent',
            QuestionType::DATE => '_date',
            QuestionType::EMAIL => '_email',
            QuestionType::OPTIONS => '_options',
            QuestionType::TEXT => '_text',
        ];

        $elView = ArrayHelper::getValue($map, $elType, $defaultInput);

        return '/recruitment/elements/' . $elView;
    }
}