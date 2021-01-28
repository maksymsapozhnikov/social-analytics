<?php

use yii\db\Migration;

/**
 * Class m180116_133717_badwords
 */
class m180116_133717_badwords extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('survey_bad_words', [
            'id' => $this->primaryKey(),
            'country' => $this->string(255)->notNull()->defaultValue('')->unique(),
            'words' => $this->text()->notNull(),
        ]);
        $this->execute('CREATE FULLTEXT INDEX ftidx_bad_words on survey_bad_words(words)');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('survey_bad_words');
    }

}
