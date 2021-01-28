<?php
namespace app\components;

use app\components\helpers\TranslateMessage;
use yii\validators\Validator;

/**
 * Class RecruitmentDobValidator
 * @package app\components
 */
class RecruitmentDobValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->message = TranslateMessage::t('survey-process', 'Please check the year of your date of birth.');
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if (!$model->hasErrors($attribute)) {
            /** @var \DateTime $value */
            $value = \DateTime::createFromFormat('d.m.Y', $model->{$attribute}, new \DateTimeZone('UTC'));
            if (!$value) {
                $model->addError($attribute, 'Invalid date');
            } else {
                $model->{$attribute} = $value->format('d.m.Y');
            }
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     * @return string|null
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<JS
        var year = Number(String(value).substr(6, 4));
        var lY = (new Date()).getFullYear() - 4;
        if (year && year >= lY) {
            messages.push($message);            
        }
JS;
    }
}