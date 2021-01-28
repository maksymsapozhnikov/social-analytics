<?php
/**
 * @var $survey \app\models\Survey
 * @var $this \yii\web\View
 */

/** @todo pack & minify these scripts */

$this->registerJsFile('@web/js/deviceatlas-custom-1.5-170605.min.js');

$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/fingerprintjs2/1.5.1/fingerprint2.min.js');

$this->registerJsFile('@web/js/app.js', [
    'depends' => [\yii\web\JqueryAsset::className()],
    \yii\web\View::POS_READY,
]);

$this->registerJs("var surveyRmsid = '{$survey->rmsid}';", \yii\web\View::POS_HEAD);
