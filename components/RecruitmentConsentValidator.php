<?php
namespace app\components;

use yii\validators\Validator;

/**
 * Class RecruitmentConsentValidator
 * @package app\components
 */
class RecruitmentConsentValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $expectedValue = ['honesty', 'agreed'];

        sort($value);
        sort($expectedValue);

        return $value === $expectedValue ? null : [''];
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     * @return string|null
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return <<<JS
        var c1 = $('#profilequestion-value--0').prop('checked');
        var c2 = $('#profilequestion-value--1').prop('checked');
        if (!c1 || !c2) {
            messages.push('');
        }
JS;
    }
}