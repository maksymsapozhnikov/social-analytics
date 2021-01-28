<?php
/* @var $surveyUrl string */

$isApple = \Yii::$app->respondentIdentity->getAtlas('properties.manufacturer') == 'Apple';

$scrolling = $isApple ? 'scrolling="no"' : '';

?>
<iframe src="<?= $surveyUrl ?>" frameborder="0" width="100%" height="100%" <?= $scrolling ?>></iframe>
