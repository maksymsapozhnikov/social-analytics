<?php

use yii\helpers\Html;
use app\components\helpers\TranslateMessage;

$this->title = TranslateMessage::t('app', 'New surveys available for you');
$this->params['breadcrumbs'][] = $this->title;

?>

<p></p>

<h2 class="text-center"><?= Html::encode($this->title) ?></h2>

<div class="row">

    <p></p>

</div>

<!-- div class="row">

    <div class="survey-card col-md-4 col-sm-6 col-xs-12">
        <div class="col-xs-12" style="padding:15px; border: 1px solid #CCCCCC">
            <div class="row" >
                <div class="col-xs-12">
                    <h3>Quite long survey name for two lines Quite long survey name for two lines</h3>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div style="font-size:1.3rem;" class="text-muted">Expires</div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div style="font-size:2rem; font-weight:600;">19.09.2017</div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div style="font-size:1.3rem;" class="text-muted">Bonus</div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <div style="font-size:2rem; font-weight:600;">Points</div>
                </div>
                <div class="col-xs-6 text-right">
                    <div style="font-size:2rem; font-weight:600;">500</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="col-xs-12" style="padding:15px; border: 1px solid #CCCCCC">
            <h4>Survey Dummy 2</h4>
        </div>
    </div>

    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="col-xs-12" style="padding:15px; border: 1px solid #CCCCCC">
            <h4>Survey Dummy 3</h4>
        </div>
    </div>

</div -->
