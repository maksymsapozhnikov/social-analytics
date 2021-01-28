<?php

use yii\db\Migration;

class m170911_211241_userTranslations extends Migration
{
    const CATEGORY = 'user';

    protected static $translations;

    protected static $languages = [
        'zh' => 'zh-CN',
    ];

    public function init()
    {
        $filename = Yii::getAlias('@vendor/dektrium/yii2-user/messages/ru/user.php');
        $source = require $filename;
        static::$translations = array_keys($source);
    }


    public function safeUp()
    {
        $languages = \app\components\helpers\TranslateMessage::getLanguages('lang');

        foreach(self::$translations as $sourceMessage) {
            $sourceModel = $this->addSourceMessage($sourceMessage);
            foreach($languages as $lang) {
                $lang = \yii\helpers\ArrayHelper::getValue(static::$languages, $lang, $lang);
                $translation = \Yii::t('user', $sourceMessage, [], $lang);
                $translation = ($translation == $sourceMessage && $lang != 'en') ? null : $translation;
                $sourceModel->addTranslation($lang, $translation);
            }
        }
    }

    protected function addSourceMessage($message)
    {
        $sourceMessage = new \app\models\translation\SourceMessage([
            'category' => self::CATEGORY,
            'message' => $message,
        ]);

        $sourceMessage->save();

        if (!$sourceMessage->id) {
            throw new \Exception('Error add translastion for: ' . $message);
        }

        return $sourceMessage;
    }

    public function safeDown()
    {
        echo "m170911_211241_userTranslations cannot be reverted.\n";

        return false;
    }
}
