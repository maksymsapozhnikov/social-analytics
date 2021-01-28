<?php

use yii\db\Migration;

/**
 * Class m190321_091115_mobi44DobValidator
 */
class m190321_091115_mobi44DobValidator extends Migration
{
    const CATEGORY = 'survey-process';

    protected static $translations = [
        'Please check the year of your date of birth.' => [
            ['en', 'Please check the year of your date of birth.'],
            ['ru', 'Пожалуйста проверьте год даты рождения.'],
        ],
    ];

    /**
     * {@inheritdoc}
     * @throws
     */
    public function safeUp()
    {
        foreach(self::$translations as $sourceMessage => $translations) {
            $sourceModel = $this->addSourceMessage($sourceMessage);
            foreach($translations as $translation) {
                $sourceModel->addTranslation($translation[0], $translation[1]);
            }
        }
    }

    /**
     * @param $message
     * @return \app\models\translation\SourceMessage
     * @throws Exception
     */
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190321_091115_mobi44DobValidator cannot be reverted.\n";

        return false;
    }
}
