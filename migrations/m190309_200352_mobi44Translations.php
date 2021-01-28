<?php

use app\components\TgmMigration;
use app\models\enums\TranslationCategoryEnum;

/**
 * Class m190309_200352_mobi44Translations
 */
class m190309_200352_mobi44Translations extends TgmMigration
{
    static $translations = [
        'err_required' => [
            'en' => 'This question is required',
            'ru' => 'Необходим ответ на этот вопрос',
            'pl' => 'Odpowiedź na to pytanie jest obowiązkowa',
        ],
    ];

    /**
     * {@inheritdoc}
     * @throws
     */
    public function safeUp()
    {
        $this->addTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);
    }

    /**
     * {@inheritdoc}
     * @throws
     */
    public function safeDown()
    {
        $this->removeTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);
    }
}
