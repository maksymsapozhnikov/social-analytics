<?php
namespace app\controllers;

use yii\web\Cookie;
use app\components\helpers\TranslateMessage;

trait UserLanguageTrait
{
    public function init()
    {
        parent::init();

        $languages = TranslateMessage::getLanguages('lang');

        $getLang = \Yii::$app->request->get('lang');

        if ($getLang && in_array($getLang, $languages)) {
            \Yii::$app->response->cookies->add(new Cookie([
                'name' => 'RMS_LANG',
                'value' => $getLang,
            ]));
            $language = $getLang;
        } else {
            $defaultLanguage = \Yii::$app->request->getPreferredLanguage($languages);
            $language = \Yii::$app->request->cookies->getValue('RMS_LANG') ?: $defaultLanguage;
        }

        \Yii::$app->language = $language;
    }
}
