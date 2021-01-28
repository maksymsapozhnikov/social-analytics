<?php

use app\models\enums\TranslationCategoryEnum;
use app\migrations\BaseTgmMigration;

/**
 * Class m190811_174039_panel544AutoRegistration
 */
class m190811_174039_panel544AutoRegistration extends BaseTgmMigration
{
    static $translations = [
        'consent' => [
            'en' => "<p>Hello, we have a great project for you. What do you have to do?</p><p>First, you need to answer a couple of simple questions. Once you answer all of them, at the end of the survey, we will send your reward!</p><p>Please click Next to start the survey.</p>",
            'ru' => "<p>Привет! У нас есть отличный опрос для вас. Что нужно делать.</p><p>Для начала, необходимо ответить на несколько простых вопросов. После того, как вы на них ответите, в конце опроса, вы получите вознаграждение.</p><p>Нажмите Далее, чтобы начать опрос.</p>",
        ],
        'consent_checkbox_honesty' => [
            'en' => 'I understand, that if I rush or answer dishonestly I will not get rewarded',
            'ru' => 'Я понимаю, что в случае нечестных ответов я не получу вознаграждение',
        ],
        'consent_checkbox_agreed' => [
            'en' => 'I agree to <b>Privacy Policy</b> and <b>Terms of Service</b>',
            'ru' => 'Я согласен с <b>Политикой Конфиденциальности</b> и <b>Условиями Использования</b>',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $t = $this->beginCommand('Adding translations');
        $this->addTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);
        $this->endCommand($t);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->removeTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);
    }
}
