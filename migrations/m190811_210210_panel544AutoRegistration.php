<?php

use app\models\enums\TranslationCategoryEnum;
use app\migrations\BaseTgmMigration;

/**
 * Class m190811_210210_panel544AutoRegistration
 */
class m190811_210210_panel544AutoRegistration extends BaseTgmMigration
{
    static $translations = [
        'please_provide_email' => [
            'en' => "<p>Please provide your email, we will register you in our panel, and check if there are any available surveys for you.</p>",
            'ru' => "<p>Пожалуйста, укажите адрес электронной почты, он необходим для регистрации в панели и проверки наличия доступных для вас опросов.</p>",
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
