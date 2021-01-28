<?php

use yii\db\Migration;

/**
 * Class m190319_155752_mobi44DsqPage
 */
class m190319_155752_mobi44DsqPage extends Migration
{
    const CATEGORY = 'survey-process';

    protected static $translations = [
        'You don\'t qualify for this project' => [
            ['en', 'You don\'t qualify for this project'],
            ['ru', 'Вы не подходите для данного опроса'],
        ],
        'Thanks! Unfortunately, you don\'t qualify for this project. However, look out for our next project, it\'s coming soon!' => [
            ['en', 'Thanks! Unfortunately, you don\'t qualify for this project. However, look out for our next project, it\'s coming soon!'],
            ['ru', 'Спасибо, к сожалению вы не подходите для этого опроса. Однако обратите внимание на наш следующий опрос, он скоро выйдет.'],
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
        echo "m190319_155752_mobi44DsqPage cannot be reverted.\n";

        return false;
    }
}