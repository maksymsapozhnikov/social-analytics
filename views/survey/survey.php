<?php
/**
 * @var $survey \app\models\Survey
 * @var $this \yii\web\View
 */

use app\components\helpers\TranslateMessage;

$this->registerJs("var surveyRmsid = '{$survey->rmsid}';", \yii\web\View::POS_HEAD);

?>
<div class="row please-wait" style="width:100%">
    <div class="col-xs-12 text-center">
        <h1 style="color:#51B2D2;text-align:center;"><?= TranslateMessage::t('survey-process', 'Looking for Survey') ?></h1>
    </div>
</div>
<div class="sk-circle please-wait">
    <div class="sk-circle1 sk-child"></div>
    <div class="sk-circle2 sk-child"></div>
    <div class="sk-circle3 sk-child"></div>
    <div class="sk-circle4 sk-child"></div>
    <div class="sk-circle5 sk-child"></div>
    <div class="sk-circle6 sk-child"></div>
    <div class="sk-circle7 sk-child"></div>
    <div class="sk-circle8 sk-child"></div>
    <div class="sk-circle9 sk-child"></div>
    <div class="sk-circle10 sk-child"></div>
    <div class="sk-circle11 sk-child"></div>
    <div class="sk-circle12 sk-child"></div>
</div>
