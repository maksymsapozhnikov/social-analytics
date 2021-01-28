<?php

use yii\db\Migration;

class m170925_174133_surveyBlockInfo extends Migration
{
    public function safeUp()
    {
        $this->addColumn('respondent', 'survey_blacklist', $this->integer()->defaultValue(null)->comment('When respondent has been blocked, survey id'));
    }

    public function safeDown()
    {
        echo "m170925_174133_surveyBlockInfo cannot be reverted.\n";

        return false;
    }
}
