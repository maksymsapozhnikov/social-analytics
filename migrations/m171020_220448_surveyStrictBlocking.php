<?php

use yii\db\Migration;

class m171020_220448_surveyStrictBlocking extends Migration
{
    public function safeUp()
    {
        $this->addColumn('survey', 'strict', $this->integer()->notNull()->defaultValue(0)->comment('Strict respondent checking'));
    }

    public function safeDown()
    {
        $this->dropColumn('survey', 'strict');
    }
}
