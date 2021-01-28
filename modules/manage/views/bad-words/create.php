<?php
/**
 * Bad words creating form
 * @var $model \app\modules\manage\models\BadWords
 */

$this->title = 'Bad words';

?>

<h1>New List</h1>

<?php

echo $this->render('_form', ['model' => $model, ]);
