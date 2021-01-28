<?php

use app\models\enums\TranslationCategoryEnum;
use app\models\translation\SourceMessage;
use yii\db\Migration;
use yii\helpers\ArrayHelper;

/**
 * Class m190308_013503_mobi44Translations
 */
class m190308_013503_mobi44Translations extends Migration
{
    static $translations = [
        'q_gender' => [
            'en' => 'Are you?',
            'ru' => 'Вы',
        ],
        'a_gender_0' => [
            'en' => 'Male',
            'ru' => 'Мужчина',
        ],
        'a_gender_1' => [
            'en' => 'Female',
            'ru' => 'Женщина',
        ],
        'q_age' => [
            'en' => 'How old are you?',
            'ru' => 'Сколько вам лет?',
        ],
        'q_have_children' => [
            'en' => 'Do you have any children?',
            'ru' => 'У вас есть дети?',
        ],
        'a_have_children_0' => [
            'en' => 'Yes - I have children',
            'ru' => 'Да, у меня есть дети',
        ],
        'a_have_children_1' => [
            'en' => 'No - I don\'t have children yet',
            'ru' => 'Нет, у меня нет детей',
        ],
        'q_martial' => [
            'en' => 'What is your marital status?',
            'ru' => 'Каково ваше семейное положение?',
        ],
        'a_martial_0' => [
            'en' => 'Living with partner / Domestic partnership',
            'ru' => 'Живем с партнером / Общее хозяйство',
        ],
        'a_martial_1' => [
            'en' => 'Married',
            'ru' => 'В браке',
        ],
        'a_martial_2' => [
            'en' => 'Single (never married)',
            'ru' => 'Живу одна/один (никогда не была в браке)',
        ],
        'a_martial_3' => [
            'en' => 'Separated',
            'ru' => 'Живем раздельно',
        ],
        'a_martial_4' => [
            'en' => 'Divorced',
            'ru' => 'В разводе',
        ],
        'a_martial_5' => [
            'en' => 'Widowed',
            'ru' => 'Вдова / вдовец',
        ],
        'a_martial_6' => [
            'en' => 'Prefer not to answer',
            'ru' => 'Предочитаю не отвечать',
        ],
        'q_children' => [
            'en' => 'How many children do you have?',
            'ru' => 'Сколько у вас детей?',
        ],
        'a_children_0' => [
            'en' => '1 child',
            'ru' => '1 ребенок',
        ],
        'a_children_1' => [
            'en' => '2 children',
            'ru' => '2 детей',
        ],
        'a_children_2' => [
            'en' => '3 children',
            'ru' => '3 детей',
        ],
        'a_children_3' => [
            'en' => '4 children',
            'ru' => '4 детей',
        ],
        'a_children_4' => [
            'en' => '5 children',
            'ru' => '5 детей',
        ],
        'a_children_5' => [
            'en' => '6 children',
            'ru' => '6 детей',
        ],
        'a_children_6' => [
            'en' => '7 children and more',
            'ru' => '7 детей или больше',
        ],
        'a_children_7' => [
            'en' => 'No - I don\'t have children yet',
            'ru' => 'Нет, у меня нет детей',
        ],
        'q_is_focused' => [
            'en' => 'How much is five + two',
            'ru' => 'Сколько будет пять плюс два?',
        ],
        'h_is_focused' => [
            'en' => 'We just check if you are still being focused',
            'ru' => 'Мы просто проверяем, что вы еще сфокусированы на вопросах',
        ],
        'q_dob' => [
            'en' => 'Enter please your date of birth.',
            'ru' => 'Укажите дату вашего рождения',
        ],
        'next' => [
            'en' => 'Next',
            'ru' => 'Далее',
        ]
    ];

    /**
     * {@inheritdoc}
     * @throws
     */
    public function safeUp()
    {
        $this->addColumn('mod_source_message', 'code', $this->string()->comment('app internal code'));

        $this->addTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);
    }

    /**
     * {@inheritdoc}
     * @throws
     */
    public function safeDown()
    {
        $this->removeTranslations(TranslationCategoryEnum::RECRUITMENT, self::$translations);

        $this->dropColumn('mod_source_message', 'code');
    }

    /**
     * Loads translations
     * @param string $category
     * @param array $translations
     * @throws Exception
     */
    protected function addTranslations($category, array $translations)
    {
        foreach ($translations as $code => $messages) {
            $sourceMessage = ArrayHelper::getValue($messages, 'en');
            if (!$sourceMessage) {
                throw new Exception('Source message not found');
            }

            $sourceModel = $this->addSourceMessage($category, $sourceMessage, $code);

            foreach ($messages as $lang => $message) {
                $sourceModel->addTranslation($lang, $message);
            }
        }
    }

    /**
     * Adds translation source message in $category
     * @param string $category
     * @param string $message
     * @param string $code
     * @return SourceMessage
     * @throws Exception
     */
    protected function addSourceMessage($category, $message, $code = '')
    {
        $sourceMessage = new SourceMessage([
            'code' => $code,
            'category' => $category,
            'message' => $message,
        ]);

        if (!$sourceMessage->save()) {
            throw new Exception('Error add translastion for: ' . $message);
        }

        return $sourceMessage;
    }

    /**
     * Removes translations, for rollback purpose
     * @param $category
     * @param array $translations
     * @throws
     */
    protected function removeTranslations($category, array $translations)
    {
        foreach ($translations as $messages) {
            $sourceMessage = ArrayHelper::getValue($messages, 'en');
            if (!$sourceMessage) {
                throw new Exception('Source message not found');
            }

            $sourceModel = SourceMessage::findOne([
                'category' => $category,
                'message' => $sourceMessage,
            ]);

            if ($sourceModel) {
                $sourceModel->delete();
            }
        }
    }
}
