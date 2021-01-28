<?php
/**
 * @var $this yii\web\View
 * @var $language \app\models\Language
 * @var $translations \yii\data\ActiveDataProvider
 */

$this->title = 'Translations: ' . $language->name;

?>

<h1><?= $this->title ?></h1>

<div class="translations-form">

    <?= $this->render('language-form', ['model' => $language]) ?>

    <?= $this->render('translations-form', ['dataProvider' => $translations, 'language' => $language]) ?>

</div>
