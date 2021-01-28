<?php
namespace app\components\helpers;

use app\models\Language;
use yii\helpers\ArrayHelper;

class TranslateMessage
{
    public static function getLanguages($type = 'name')
    {
        $type = in_array($type, ['lang', 'name', 'native_name']) ? $type : 'name';

        $languages = ArrayHelper::toArray(Language::find()->all(), [
            Language::className() => ['lang', $type],
        ]);

        return ArrayHelper::map($languages, 'lang', $type);
    }

    /**
     * Translates given message.
     * Copies Yii::t interface to be able to use app_i18n service instead of i18n.
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (\Yii::$app !== null) {
            return \Yii::$app->app_i18n->translate($category, $message, $params, $language ?: \Yii::$app->language);
        }

        $placeholders = [];
        foreach ((array) $params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }

}
