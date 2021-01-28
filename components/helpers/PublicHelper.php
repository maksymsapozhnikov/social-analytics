<?php
namespace app\components\helpers;

use app\models\Profile;
use yii\helpers\Html;

class PublicHelper
{
    public static function getUsername()
    {
        if (\Yii::$app->user->isGuest) {
            return 'Guest';
        }

        /** @var Profile $profile */
        $profile = \Yii::$app->user->identity->profile;

        return $profile->name . ' ' . $profile->last_name;
    }

    public static function facebookButton()
    {
        return Html::tag('span', '', ['class' => 'fa fa-facebook'])
            . TranslateMessage::t('user', 'Sign in with Facebook');
    }
}
