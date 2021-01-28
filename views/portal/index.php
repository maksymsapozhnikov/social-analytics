<?php
/**
 * @var \yii\web\View $this
 */

use app\components\helpers\TranslateMessage;

$messages = [
    'se' => TranslateMessage::t('recruitment', 'An error occurred. Please try again'),
    's0' => TranslateMessage::t('recruitment', 'Registering your profile'),
    's1' => TranslateMessage::t('recruitment', 'We are looking surveys for you'),
    's2' => TranslateMessage::t('recruitment', 'Your email is already is use. Please sign in'),
    's3' => TranslateMessage::t('recruitment', 'We do not have new surveys for you yet. Please try again later'),
];

$this->registerJs("var regMsgs = " . \yii\helpers\Json::encode($messages) . ";", \yii\web\View::POS_HEAD);

?>
<div class="row please-wait" style="width:100%">
    <div class="col-xs-12 text-center">
        <h1 style="color:#51B2D2;text-align:center;" id="message"><?= TranslateMessage::t('recruitment', 'Registering your profile') ?></h1>
    </div>
</div>

<div class="sk-circle please-wait" id="loader-circle">
    <div class="sk-circle1 sk-child"></div>
    <div class="sk-circle2 sk-child"></div>
    <div class="sk-circle3 sk-child"></div>
    <div class="sk-circle4 sk-child"></div>
    <div class="sk-circle5 sk-child"></div>
    <div class="sk-circle6 sk-child"></div>
    <div class="sk-circle7 sk-child"></div>
    <div class="sk-circle8 sk-child"></div>
    <div class="sk-circle9 sk-child"></div>
    <div class="sk-circle10 sk-child"></div>
    <div class="sk-circle11 sk-child"></div>
    <div class="sk-circle12 sk-child"></div>
</div>

<div class="row text-center" id="block-sign-in" style="margin-top:200px;display:none">
    <a class="btn btn-primary btn-lg" style="width:90%" href="https://portal.tgmpanel.com">Sign in</a>
</div>

<div class="row text-center" id="block-try-again" style="margin-top:200px;display:none">
    <button class="btn btn-primary btn-lg" style="width:90%">Retry</button>
</div>

<?php

$script = <<<'JS'
$('#block-sign-in').hide();

var fnOnRegisteredSuccess = function(response) {
    $('#block-try-again').hide();
    $('#block-sign-in').hide();
    $('#loader-circle').show();
    $('#message').text(regMsgs.s1);
    $.get('/portal/survey')
        .done(function(response) {
            document.location = response;
        })
        .fail(function() {
            if (arguments[0].status === 404) {
                $('#message').text(regMsgs.s3);
                $('#loader-circle').hide();
                $('#block-sign-in').show();
                return;
            }

            $('#block-try-again button').off('click').on('click', fnOnRegisteredSuccess);
            $('#message').text(regMsgs.se);
            $('#loader-circle').hide();
            $('#block-try-again').show();
        });
};

var fnOnStartRegistering = function () {
    $('#block-try-again').hide();
    $('#block-sign-in').hide();
    $('#loader-circle').show();
    $('#message').text(regMsgs.s0);

    $.post('/portal/register')
        .done(fnOnRegisteredSuccess)
        .fail(function() {
            if (arguments[0].status === 422) {
                $('#message').text(regMsgs.s2);
                $('#loader-circle').hide();
                $('#block-sign-in').show();
                return;
            }
    
            $('#block-try-again button').off('click').on('click', fnOnStartRegistering);
            $('#message').text(regMsgs.se);
            $('#loader-circle').hide();
            $('#block-try-again').show();
        });
};

fnOnStartRegistering();

JS;

$this->registerJs($script, \yii\web\View::POS_READY);