<?php
/**
 * @var ProfileQuestion $question
 * @var ActiveForm $form
 * @var View $this
 */

use app\models\enums\TranslationCategoryEnum;
use app\components\helpers\TranslateMessage;
use app\components\recruitment\ProfileQuestion;
use kartik\form\ActiveForm;
use yii\web\View;

$labelText = TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, $question->title);
$hintText = TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, $question->hint);

$message = \app\models\translation\SourceMessage::findOne(['code' => 'please_provide_email']);

echo $message->getTranslation(Yii::$app->language) ?? $message->getTranslation('en');

echo $form->field($question, 'value')
    ->textInput(['type' => 'email'])
    ->label($labelText)
    ->hint($hintText ?: false);
