<?php
/**
 * @var ProfileQuestion $question
 * @var ActiveForm $form
 * @var View $this
 */

use app\models\translation\SourceMessage;
use app\components\recruitment\ProfileQuestion;
use kartik\form\ActiveForm;
use yii\web\View;

$message = SourceMessage::findOne(['code' => 'consent']);
$checkbox1 = SourceMessage::findOne(['code' => 'consent_checkbox_honesty']);
$checkbox2 = SourceMessage::findOne(['code' => 'consent_checkbox_agreed']);

$lang = Yii::$app->language;

echo $message->getTranslation($lang) ?? $message ->getTranslation('en');

echo $form->field($question, 'value')
    ->label(false)
    ->checkboxList([
        'honesty' => $checkbox1->getTranslation($lang) ?? $checkbox1->getTranslation('en'),
        'agreed' => $checkbox2->getTranslation($lang) ?? $checkbox2->getTranslation('en'),
    ]);
