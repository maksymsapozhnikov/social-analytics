<?php
/**
 * @var $this yii\web\View
 * @var $survey \app\models\Survey
 */

$this->title = 'New Survey';

?>

<h1><?= $this->title ?></h1>

<div class="company-form">

    <?= $this->render('survey-form', ['survey' => $survey]) ?>

</div>
