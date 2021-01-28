<?php

use app\models\enums\TranslationCategoryEnum;

/**
 * Class m190826_224629_panel544Translations
 */
class m190826_224629_panel544Translations extends \app\migrations\BaseTgmMigration
{
    static $translations = [
        [
            'en' => 'An error occurred. Please try again',
            'ru' => 'Произошла ошибка. Попробуйте еще раз',
        ],
        [
            'en' => 'Registering your profile',
            'ru' => 'Регистрируем ваш профиль',
        ],
        [
            'en' => 'We are looking surveys for you',
            'ru' => 'Ищем подходящие для вас опросы',
        ],
        [
            'en' => 'Your email is already is use. Please sign in',
            'ru' => 'Ваш адрес электронной почты уже зарегистрирован',
        ],
        [
            'en' => 'We do not have new surveys for you yet. Please try again later',
            'ru' => 'Сейчас у нас нет для вас новых опросов. Попробуйте позже',
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
