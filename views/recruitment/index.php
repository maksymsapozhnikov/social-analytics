<?php
/**
 * @var ProfileQuestion $question
 * @var Survey $survey
 * @var View $this
 */

use app\models\enums\TranslationCategoryEnum;
use app\components\helpers\TranslateMessage;
use app\components\recruitment\ProfileQuestion;
use app\components\RecruitmentHelper;
use app\models\Survey;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

?>
<div class="col-xs-12 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">

    <?php

    $form = ActiveForm::begin([
        'method' => 'post',
        'action' => Url::to(["/rcs/{$survey->rmsid}"]),
        'id' => 'rcsrv',
    ]);

    echo $form->field($question, 'uuid')->hiddenInput()->label(false);

    echo $this->render(RecruitmentHelper::getElementView($question->type), [
        'question' => $question,
        'survey' => $survey,
        'form' => $form,
    ]);

    $nextButton = TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, 'Next');
    $pleaseWait = TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, 'Please wait');

    echo Html::submitButton($nextButton, [
        'id' => 'rcsrv-submit-btn',
        'class' => 'btn btn-primary form-control',
        'style' => 'margin-top:25px; border-color:#6849A2; background-color:#6849A2',
    ]);

    ActiveForm::end();

    $script = <<<JS
    $('#rcsrv').on('beforeValidate', function (e) {
        $('#rcsrv-submit-btn').attr('disabled', true);
    });
    
    $('#rcsrv').on('afterValidate', function (e) {
        $('#rcsrv-submit-btn').attr('disabled', false);
    });
    
    $('#rcsrv').on('beforeSubmit', function (e) {
        $('#rcsrv-submit-btn').text('{$pleaseWait}');
        $('#rcsrv-submit-btn').attr('disabled', true);
        return true;
    });
JS;
    $this->registerJs($script, \yii\web\View::POS_LOAD);

    ?>

</div>
