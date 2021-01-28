<?php
/**
 * Bad words editing form
 * @var $model \app\modules\manage\models\BadWords
 */

$this->title = 'Edit';

?>

<h1>Edit List: <?= $model->country ?></h1>

<?php

echo $this->render('_form', ['model' => $model, ]);
