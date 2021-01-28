<?php

use app\components\TgmMigration;

/**
 * Class m191008_103124_mobi82Translation
 */
class m191008_103124_mobi82Translation extends TgmMigration
{
    static $translations = [
        [
            'en' => 'Please join our panel',
            'ru' => 'Присоединяйтесь к нашей панели',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addTranslations('survey-process', self::$translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->removeTranslations('survey-process', self::$translations);
    }
}
