<?php

use yii\db\Migration;

/**
 * Class m190203_161216_mobi44Recruitment
 */
class m190203_161216_mobi44Recruitment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'tgm_recruitment', $this->boolean()->notNull()->defaultValue(false));

        $this->createTable('recruitment_profile', [
            'id' => $this->primaryKey(),
            'respondent_id' => $this->integer()->notNull()->unique(),
            'content' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('recruitment_profile');

        $this->dropColumn('survey', 'tgm_recruitment');
    }
}