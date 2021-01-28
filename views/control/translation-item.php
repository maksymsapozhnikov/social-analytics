<?php
/**
 * @var \app\models\Translation $data
 */

use yii\helpers\StringHelper;

?>
<div class="row">
    <div class="col-xs-2 text-muted text-right">Greeting message:</div>
    <div class="col-xs-10">
        <?= $data->msg_1_hello ?>
    </div>
</div>
<div class="row" style="margin-top:7px">
    <div class="col-xs-2 text-muted text-right">Survey closed:</div>
    <div class="col-xs-10">
        <?= StringHelper::truncateWords(strip_tags(str_replace('>', '> ', $data->msg_2_closed)), 60); ?>
    </div>
</div>
<div class="row" style="margin-top:7px">
    <div class="col-xs-2 text-muted text-right">Wrong phone:</div>
    <div class="col-xs-10">
        <?= $data->msg_3_wrong_phone ?>
    </div>
</div>
<div class="row" style="margin-top:7px">
    <div class="col-xs-2 text-muted text-right">Wrong currency:</div>
    <div class="col-xs-10">
        <?= $data->msg_4_wrong_currency ?>
    </div>
</div>
<div class="row" style="margin-top:7px">
    <div class="col-xs-2 text-muted text-right">Postpaid phone:</div>
    <div class="col-xs-10">
        <?= $data->msg_5_postpaid ?>
    </div>
</div>
<div class="row" style="margin-top:7px">
    <div class="col-xs-2 text-muted text-right">Payed already:</div>
    <div class="col-xs-10">
        <?= $data->msg_6_payed ?>
    </div>
</div>