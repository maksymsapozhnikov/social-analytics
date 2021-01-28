<?php

use yii\db\Migration;

class m171112_194156_surveybotBinding extends Migration
{
    public function safeUp()
    {
        $this->addColumn('respondent', 'sb_respondent_id', $this->integer());
        $this->createIndex('idxRespondentSurveybot', 'respondent', ['sb_respondent_id']);
    }

    public function safeDown()
    {
        $this->dropIndex('idxRespondentSurveybot', 'respondent');
        $this->dropColumn('respondent', 'sb_respondent_id');
    }
}
