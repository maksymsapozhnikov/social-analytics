<?php
/**
 * @var yii\web\View $this
 * @var string $title
 * @var string $message
 */

use yii\helpers\Html;

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="jumbotron jumbotron-form" style="padding-top: 5vh;">

    <h2><?= Html::encode($this->title) ?></h2>

    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <div style="font-size:1.7rem" class="text-center">
                <?= $message ?>
            </div>
        </div>

        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3" style="margin-top:25vh">
            <?= Html::a(\app\components\helpers\TranslateMessage::t('user', 'Continue'), ['/'], ['class' => 'btn btn-danger btn-block']) ?>
        </div>
    </div>

</div>
