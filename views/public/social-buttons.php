<?php

use yii\authclient\widgets\AuthChoice;
use app\components\helpers\PublicHelper;

?>
<div class="row" style="padding-top: 4vh; margin:0 !important; font-size:14px !important;">
    <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
        <div class="auth-container col-xs-12 col-md-8 col-md-offset-2">
            <?php
            $authAuthChoice = AuthChoice::begin([
                'baseAuthUrl' => ['/user/security/auth'],
            ]);

            foreach ($authAuthChoice->getClients() as $client) {
                if ('facebook' == $client->id) {
                    echo $authAuthChoice->clientLink($client, PublicHelper::facebookButton(), [
                        'class' => 'btn btn-block btn-social btn-' . $client->id,
                        'style' => 'font-size:14px;padding:7px 0 7px 45px',
                    ]);
                }
            }

            AuthChoice::end();
            ?>
        </div>
    </div>
</div>
