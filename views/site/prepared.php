<?php
/**
 * @var yii\web\View $this
 * @var string $html prepared page
 * @var string $join prepared page
 * @var string $title page title
 * @var string $urlEnd
 */

$this->title = \Yii::$app->name . ': ' . $title;

use app\components\helpers\TranslateMessage;
use app\models\enums\TranslationCategoryEnum;
use yii\helpers\Html;

$hasJoinUrl = isset($urlEnd) && !!$urlEnd;
$hasJoinText = $hasJoinUrl && isset($join) && !!$join;

?>

<div class="row">
    <div class="col-lg-2 col-xs-1"></div>
    <div class="col-lg-8 col-xs-10">
        <h1 class="div-text-title"><?= $title ?></h1>
        <div class="div-textblock-1"><?= $html ?></div>
        <?php if ($hasJoinText) { ?>
            <div class="div-textblock-2"><?= $join ?></div>
        <?php } ?>
    </div>
    <div class="col-lg-2 col-xs-1"></div>
</div>
<?php if ($hasJoinUrl) { ?>
    <div class="row">
    </div>
    <div class="row">
        <div class="col-lg-2 col-xs-1"></div>
        <div class="col-lg-8 col-xs-10">
            <?= Html::button(TranslateMessage::t(TranslationCategoryEnum::SURVEY_PROCESS, 'Yes'), [
                'class' => 'btn btn-primary form-control',
                'style' => 'margin-top:25px; border-color:#6849A2; background-color:#6849A2',
            ]) ?>
        </div>
        <div class="col-lg-2 col-xs-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-2 col-xs-1"></div>
        <div class="col-lg-8 col-xs-10">
            <?= Html::button(TranslateMessage::t(TranslationCategoryEnum::SURVEY_PROCESS, 'No'), [
                'class' => 'btn btn-primary form-control',
                'style' => 'margin-top:25px; border-color:#6849A2; background-color:#6849A2',
            ]) ?>
        </div>
        <div class="col-lg-2 col-xs-1"></div>
    </div>
<?php }

$js = <<<JS
    $('.btn').click(function() {
      document.location = '{$urlEnd}';
    });
JS;
$this->registerJs($js, \yii\web\View::POS_LOAD);