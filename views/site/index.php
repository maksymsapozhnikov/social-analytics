<?php

use app\components\helpers\TranslateMessage;

$this->title = 'TGM Research';

?>
<div class="jumbotron">
    <?php

    $title =  TranslateMessage::t('app', 'Take surveys, choose a reward');
    $signup = \app\components\helpers\TranslateMessage::t('user', 'Sign up');
    $login = \app\components\helpers\TranslateMessage::t('user', 'Already registered? Sign in!');

    ?>
    <div class="container-liquid">
        <div class="row" style="padding-top:40vh;margin:0">
            <div class="col-lg-6 col-lg-offset-6">
                <h1><?= $title ?></h1>
                <div class="row" style="margin:5vh 0 3vh 0">
                    <a href="<?= \yii\helpers\Url::to(['/sign-up']) ?>" class="btn btn-danger" style="width:250px"><?= $signup ?></a>
                </div>

                <a href="<?= \yii\helpers\Url::to(['/login']) ?>"><?= $login ?></a>
            </div>
        </div>
    </div>
</div>
