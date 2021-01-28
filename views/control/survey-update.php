<?php
/**
 * @var $this yii\web\View
 * @var $survey \app\models\Survey
 */

$this->title = 'Update Survey';

?>

<h1><?= $this->title ?> <?= $survey->rmsid ?></h1>

<div class="company-form">

    <?= $this->render('survey-form', ['survey' => $survey]) ?>

</div>
