<?php
/**
 * @var ProfileQuestion $question
 * @var ActiveForm $form
 * @var View $this
 */

use app\components\helpers\DateHelper;
use app\components\helpers\TranslateMessage;
use app\components\recruitment\ProfileQuestion;
use app\models\enums\TranslationCategoryEnum;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$labelText = TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, $question->title);
$hintText = TranslateMessage::t(TranslationCategoryEnum::RECRUITMENT, $question->hint);

?>
    <label class="control-label" for="rs-date"><?= TranslateMessage::t('user', $labelText) ?></label>
    <?= $form->field($question, 'value')->hiddenInput(['id' => 'rs-value'])->label(false) ?>
    <div id="rs-date" class="control-label row">
        <div class="col-xs-3" style="padding: 0 5px">
            <?= Html::dropDownList('rs-day', null, DateHelper::getDaysArray(), [
                'id' => 'rs-day',
                'class' => 'form-control field-number',
                'options' => ['0' => ['disabled' => true]],
                'style' => 'margin-bottom: 5px',
            ]) ?>
        </div>
        <div class="col-xs-5" style="padding: 0 5px">
            <?= Html::dropDownList('rs-month', null, DateHelper::getMonthsArray(), [
                'id' => 'rs-month',
                'class' => 'form-control field-number',
                'options' => ['0' => ['disabled' => true]],
                'style' => 'margin-bottom: 5px',
            ]) ?>
        </div>
        <div class="col-xs-4" style="padding: 0 5px">
            <?= Html::dropDownList('rs-year', null, DateHelper::getYearsArray(), [
                'id' => 'rs-year',
                'class' => 'form-control field-number',
                'options' => ['0' => ['disabled' => true]],
                'style' => 'margin-bottom: 5px',
            ]) ?>
        </div>
    </div>
<?php

$js = <<<'JS'
    var fnFillValue = function() {
        var y = $('#rs-year').val();
        var m = $('#rs-month').val();
        var d = $('#rs-day').val();

        $('#rs-value').val(d + '.' + m + '.' + y);
    };
    $('form').on('beforeValidate', fnFillValue);
    fnFillValue();
JS;
$this->registerJs($js, \yii\web\View::POS_LOAD);