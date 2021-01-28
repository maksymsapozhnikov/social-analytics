<?php

use yii\db\Migration;

/**
 * Class m190606_114802_mobi57LandingPages
 */
class m190606_114802_mobi57LandingPages extends Migration
{
    const CATEGORY = 'survey-process';

    protected static $translations = [
        'Good job!' => [
            ['en', 'Good job!'],
            ['ru', 'Отлично!'],
        ],
        'This is the last screen - good job! Your reward is on the way to you!' => [
            ['en', 'This is the last screen - good job! Your reward is on the way to you!'],
            ['ru', 'Отлично, это последний экран! Ваша награда уже на пути к вам!'],
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
        echo "m190606_114802_mobi57LandingPages cannot be reverted.\n";

        return false;
    }
}
