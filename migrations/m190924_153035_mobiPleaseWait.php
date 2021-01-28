<?php

use app\components\TgmMigration;
use app\models\enums\TranslationCategoryEnum;

/**
 * Class m190924_153035_mobiPleaseWait
 */
class m190924_153035_mobiPleaseWait extends TgmMigration
{
    static $translations = [
        [
            'en' => 'Please wait',
            'ru' => 'Пожалуйста подождите',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->removeTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);
    }
}
